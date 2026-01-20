<?php

namespace Paneon\PhpToTypeScript\Services;

use Paneon\PhpToTypeScript\Annotation\Exclude;
use Paneon\PhpToTypeScript\Annotation\Type;
use Paneon\PhpToTypeScript\Annotation\TypeScriptInterface;
use Paneon\PhpToTypeScript\Annotation\VirtualProperty;
use Paneon\PhpToTypeScript\Attribute\TypeScript;
use Paneon\PhpToTypeScript\Attribute\TypeScriptInterface as TypeScriptInterfaceAttribute;
use Paneon\PhpToTypeScript\Parser\DeclareEnum;
use Paneon\PhpToTypeScript\Parser\DeclareEnumCase;
use Paneon\PhpToTypeScript\Parser\DeclareInterface;
use Paneon\PhpToTypeScript\Parser\DeclareInterfaceProperty;
use Paneon\PhpToTypeScript\Parser\PhpDocParser;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\ParserFactory;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use ReflectionEnum;
use ReflectionEnumBackedCase;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionProperty;

class ParserService
{
    protected $parser;

    protected $fs;


    protected $currentInterface;

    protected string $prefix = '';

    protected string $suffix = '';

    protected int $indent = 2;

    protected bool $includeTypeNullable = false;

    protected bool $useType = false;

    protected bool $export = false;

    protected bool $useEnumUnionType = false;

    public function __construct(protected LoggerInterface $logger, protected PhpDocParser $docParser)
    {
        $this->parser = (new ParserFactory())->createForNewestSupportedVersion();
    }

    public function getInterfaceContent(
        string $sourceFileName,
               $requireAnnotation = true
    ): ?string
    {
        $stmts = $this->getStatements($sourceFileName);
        $fullClassName = $this->getFullyQualifiedClassName($stmts, $sourceFileName);

        try {
            $reflectionClass = new ReflectionClass($fullClassName);
        } catch (ReflectionException $exception) {
            $this->logger->debug(
                'Error creating ReflectionClass of ' . $fullClassName,
                [
                    'exception' => $exception
                ]
            );
            return null;
        }

        if ($requireAnnotation && !$this->hasInterfaceAnnotation($reflectionClass)) {
            return null;
        }

        $this->buildInterface($reflectionClass);

        return $this->currentInterface;
    }

    public function getStatements(string $sourceFileName)
    {
        $code = file_get_contents($sourceFileName);

        return $this->parser->parse($code);
    }

    public function getOutputFileName(string $sourceFileName): string
    {
        $sourceFileInfo = pathinfo($sourceFileName);
        $extension = $this->useType ? '.ts' : '.d.ts';
        $targetFile = $this->prefix . $sourceFileInfo['filename'] . $this->suffix . $extension;

        return $targetFile;
    }

    private function getFullyQualifiedClassName(array $stmts, $sourceFileName): ?string
    {
        $pathinfo = pathinfo($sourceFileName);

        foreach ($stmts as $statement) {
            if ($statement instanceof Namespace_) {
                $namespace = $statement->name?->toString();
                if (!empty($namespace)) {
                    return $namespace . '\\' . $pathinfo['filename'];
                }

                // global namespace
                return $pathinfo['filename'];
            }
        }

        return null;
    }

    private function hasInterfaceAnnotation(ReflectionClass $reflectionClass): bool
    {
        return (bool) $reflectionClass->getAttributes(TypeScript::class)
            || (bool) $reflectionClass->getAttributes(TypeScriptInterface::class)
            || (bool) $reflectionClass->getAttributes(TypeScriptInterfaceAttribute::class);
    }

    public function buildInterface(ReflectionClass $class)
    {
        $this->logger->debug('---------- New Interface for: ' . $class->getName() . ' ----------');
        $this->currentInterface = new DeclareInterface($class->getShortName());

        if ($this->prefix) {
            $this->currentInterface->setPrefix($this->prefix);
        }
        if ($this->suffix) {
            $this->currentInterface->setSuffix($this->suffix);
        }
        if ($this->indent) {
            $this->currentInterface->setIndent($this->indent);
        }

        $this->currentInterface->setUseType($this->useType);
        $this->currentInterface->setExport($this->export);

        $properties = $class->getProperties();
        $methods = $class->getMethods();

        foreach ($properties as $property) {
            $type = $this->detectPropertyType($property);
            $this->logger->info('- Property: ' . $property->getName() . ': ' . $type);

            if ($this->isExcluded($property)) {
                $this->logger->debug('=> isExcluded');
                continue;
            }

            $overwriteType = $this->getTypeScriptType($property);
            if ($overwriteType) {
                $this->logger->debug('- Overwrite Type: ' . $overwriteType->getType());
                $type = $overwriteType->getType();
            }

            $newProp = new DeclareInterfaceProperty($property->getName(), $type);
            $this->currentInterface->addProperty($newProp);
        }

        foreach ($methods as $method) {
            $this->logger->debug('- Method: ' . $method->getName());

            if (!$this->isVirtualProperty($method)) {
                $this->logger->debug('=> Skip: is no virtual property');
                continue;
            }

            $phpDoc = $method->getDocComment();
            $type = $method->hasReturnType()
                ? $this->docParser->getTypeEquivalent((string)$method->getReturnType(), $this->includeTypeNullable)
                : $this->docParser->parseDocComment(
                    $phpDoc,
                    PhpDocParser::PROPERTY_TYPE_METHOD,
                    $this->includeTypeNullable
                );

            $newProp = new DeclareInterfaceProperty($method->getName(), $type);
            $this->currentInterface->addProperty($newProp);
        }
    }

    public function debugProperty(ReflectionProperty $reflectionProperty)
    {
        $this->logger->debug('Property: ');
        $this->logger->debug('-- Name: ' . $reflectionProperty->getName());
        $this->logger->debug('-- Type: ' . $reflectionProperty->getType());
        $this->logger->debug('-- DocComment: ' . $reflectionProperty->getDocComment());
        $this->logger->debug('-- Default Value: ' . $reflectionProperty->getDefaultValue());
        $this->logger->debug('-- Attributes: ' . join(", ", $reflectionProperty->getAttributes()));
        $this->logger->debug('-- Modifiers: ' . $reflectionProperty->getModifiers());
        $this->logger->debug('-- Declaring Class: ' . $reflectionProperty->getDeclaringClass()->getName());
    }

    public function getTypeScriptType(ReflectionProperty $reflectionProperty): ?Type
    {
        $typeAttributes = $reflectionProperty->getAttributes(Type::class);
        if (count($typeAttributes)) {
            $typeAttribute = $typeAttributes[0]->getArguments()[0] ?? null;
            $this->logger->debug('-- hasTypeAttribute: ' . $typeAttribute);
            return new Type(['value' => $typeAttribute]);
        }

        return null;
    }

    public function isExcluded(ReflectionProperty $reflectionProperty): bool
    {
        return (bool) $reflectionProperty->getAttributes(Exclude::class);
    }

    public function isVirtualProperty(ReflectionMethod $reflectionMethod): bool
    {
        return (bool) $reflectionMethod->getAttributes(VirtualProperty::class);
    }

    public function setIndent($indent): ParserService
    {
        $this->indent = $indent;
        return $this;
    }

    public function setPrefix($prefix): ParserService
    {
        $this->prefix = $prefix;
        return $this;
    }

    public function setSuffix($suffix): ParserService
    {
        $this->suffix = $suffix;
        return $this;
    }

    public function setIncludeTypeNullable($includeTypeNullable): ParserService
    {
        $this->includeTypeNullable = $includeTypeNullable;
        return $this;
    }

    public function setUseType(bool $useType): ParserService
    {
        $this->useType = $useType;
        return $this;
    }

    public function setExport(bool $export): ParserService
    {
        $this->export = $export;
        return $this;
    }

    public function detectPropertyType(ReflectionProperty $property): string
    {
        $phpDoc = $property->getDocComment();
        $type = 'any';

        if (!empty($phpDoc)) {
            $type = $this->docParser->parseDocComment(
                $phpDoc,
                PhpDocParser::PROPERTY_TYPE_VARIABLE,
                $this->includeTypeNullable
            );
        }

        if ($type !== 'any' || PHP_VERSION_ID < 70400) {
            return $type;
        }

        $reflectionType = $property->getType();
        if ($reflectionType instanceof ReflectionNamedType) {
            $name = $reflectionType->getName();

            if (!$reflectionType->isBuiltin()) {
                if (class_exists($name)) {
                    $cls = new ReflectionClass($name);
                    $name = $cls->getShortName();
                } else {
                    $this->logger->debug('Class reference not found: ' . $name);
                }
            }

            $type = $this->docParser->getTypeEquivalent($name, $this->includeTypeNullable);
            if ($this->includeTypeNullable && $reflectionType->allowsNull()) {
                $type .= '|null';
            }
        }

        return $type;
    }

    public function getEnumContent(
        string $sourceFileName,
        bool $requireAnnotation = true
    ): ?string {
        $stmts = $this->getStatements($sourceFileName);
        $fullClassName = $this->getFullyQualifiedClassName($stmts, $sourceFileName);

        if (!enum_exists($fullClassName)) {
            $this->logger->debug('Not an enum: ' . $fullClassName);
            return null;
        }

        try {
            $reflectionEnum = new ReflectionEnum($fullClassName);
        } catch (ReflectionException $exception) {
            $this->logger->debug(
                'Error creating ReflectionEnum of ' . $fullClassName,
                [
                    'exception' => $exception
                ]
            );
            return null;
        }

        if ($requireAnnotation && !$this->hasEnumAnnotation($reflectionEnum)) {
            return null;
        }

        return $this->buildEnum($reflectionEnum);
    }

    private function hasEnumAnnotation(ReflectionEnum $reflectionEnum): bool
    {
        return (bool) $reflectionEnum->getAttributes(TypeScript::class);
    }

    public function buildEnum(ReflectionEnum $enum): string
    {
        $this->logger->debug('---------- New Enum for: ' . $enum->getName() . ' ----------');

        $declareEnum = new DeclareEnum($enum->getShortName());

        if ($this->prefix) {
            $declareEnum->setPrefix($this->prefix);
        }
        if ($this->suffix) {
            $declareEnum->setSuffix($this->suffix);
        }
        if ($this->indent) {
            $declareEnum->setIndent($this->indent);
        }

        $declareEnum->setExport($this->export);

        // Check for asUnion attribute option (null means use global setting)
        $asUnion = $this->useEnumUnionType;
        $attributes = $enum->getAttributes(TypeScript::class);
        if (count($attributes)) {
            $attribute = $attributes[0]->newInstance();
            if ($attribute->asUnion !== null) {
                $asUnion = $attribute->asUnion;
            }
        }
        $declareEnum->setAsUnion($asUnion);

        // Add enum cases
        foreach ($enum->getCases() as $case) {
            $value = null;
            if ($case instanceof ReflectionEnumBackedCase) {
                $value = $case->getBackingValue();
            }
            $declareEnumCase = new DeclareEnumCase($case->getName(), $value);
            $declareEnum->addCase($declareEnumCase);
        }

        return (string) $declareEnum;
    }

    public function setUseEnumUnionType(bool $useEnumUnionType): ParserService
    {
        $this->useEnumUnionType = $useEnumUnionType;
        return $this;
    }
}
