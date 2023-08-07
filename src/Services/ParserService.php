<?php

namespace Paneon\PhpToTypeScript\Services;

use Doctrine\Common\Annotations\Reader;
use Paneon\PhpToTypeScript\Annotation\Exclude;
use Paneon\PhpToTypeScript\Annotation\Type;
use Paneon\PhpToTypeScript\Annotation\TypeScriptInterface;
use Paneon\PhpToTypeScript\Annotation\VirtualProperty;
use Paneon\PhpToTypeScript\Parser\DeclareInterface;
use Paneon\PhpToTypeScript\Parser\DeclareInterfaceProperty;
use Paneon\PhpToTypeScript\Parser\PhpDocParser;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\ParserFactory;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionProperty;

class ParserService
{
    protected $parser;

    protected $fs;

    /**
     * @var Reader
     */
    protected $reader;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    protected $currentInterface;

    /**
     * @var PhpDocParser
     */
    protected $docParser;

    protected $prefix;

    protected $suffix;

    protected $indent;

    protected $includeTypeNullable;

    public function __construct(Reader $reader, LoggerInterface $logger, PhpDocParser $docParser)
    {
        $this->parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $this->reader = $reader;
        $this->logger = $logger;
        $this->docParser = $docParser;
    }

    public function getInterfaceContent(
        string $sourceFileName,
        $requireAnnotation = true
    ): ?string {
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
        $targetFile = $this->prefix . $sourceFileInfo['filename'] . $this->suffix . '.d.ts';

        return $targetFile;
    }

    private function getFullyQualifiedClassName(array $stmts, $sourceFileName): ?string
    {
        $pathinfo = pathinfo($sourceFileName);

        foreach ($stmts as $statement) {
            if ($statement instanceof Namespace_) {
                return implode('\\', $statement->name->parts) . '\\' . $pathinfo['filename'];
            }
        }

        return null;
    }

    private function hasInterfaceAnnotation(ReflectionClass $reflectionClass)
    {
        return $reflectionClass->getAttributes(TypeScriptInterface::class) || $this->reader->getClassAnnotation($reflectionClass, TypeScriptInterface::class);
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

        $properties = $class->getProperties();
        $methods = $class->getMethods();

        foreach ($properties as $property) {
            $this->logger->debug('- Property: ' . $property->getName());

            if ($this->isExcluded($property)) {
                $this->logger->debug('=> isExcluded');
                continue;
            }

            if ($this->getTypeScriptType($property)) {
                $type = $hasTypeScriptType->getType();
                $this->logger->debug('- Overwrite Type: ' . $type);
            } else {
                $type = $this->detectPropertyType($property);
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
                ? $this->docParser->getTypeEquivalent((string) $method->getReturnType(), $this->includeTypeNullable)
                : $this->docParser->parseDocComment(
                    $phpDoc,
                    PhpDocParser::PROPERTY_TYPE_METHOD,
                    $this->includeTypeNullable
                );

            $newProp = new DeclareInterfaceProperty($method->getName(), $type);
            $this->currentInterface->addProperty($newProp);
        }
    }

    public function getTypeScriptType(ReflectionProperty $reflectionProperty): ?Type
    {
        $attr = $reflectionProperty->getAttributes(Type::class);
        return $attr
            || $this->reader->getPropertyAnnotation($reflectionProperty, Type::class);
    }

    public function isExcluded(ReflectionProperty $reflectionProperty): bool
    {
        return $reflectionProperty->getAttributes(Exclude::class)
            || $this->reader->getPropertyAnnotation($reflectionProperty, Exclude::class);
    }

    public function isVirtualProperty(ReflectionMethod $reflectionMethod): bool
    {
        return $reflectionMethod->getAttributes(VirtualProperty::class)
            || $this->reader->getMethodAnnotation($reflectionMethod, VirtualProperty::class);
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
}
