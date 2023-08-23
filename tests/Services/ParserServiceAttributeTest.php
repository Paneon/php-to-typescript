<?php

namespace Paneon\PhpToTypeScript\Tests\Services;

use Paneon\PhpToTypeScript\Tests\AbstractTestCase;
use Paneon\PhpToTypeScript\Tests\Fixtures\AttributeClass;
use ReflectionException;
use ReflectionProperty;

class ParserServiceAttributeTest extends AbstractTestCase
{

    public function testTriggersOnClassesWithCustomAnnotation()
    {
        $content = $this->loadFixture();

        $this->assertNotNull($content);
    }

    public function testDoesntContainExcludedProperties()
    {
        $content = $this->loadFixture();

        $this->assertStringNotContainsString('excluded', $content);
    }

    public function testContainsVirtualProperties()
    {
        $content = $this->loadFixture();

        $this->assertStringContainsString('hasSomeValue: boolean;', $content);
        $this->assertStringContainsString('virtualWithReturnType: number', $content);
    }

    public function testAddsPrefixAndSuffixToClassInstances()
    {
        $fixture = $this->getDefaultFixtureFile();

        $this->parserService->setPrefix('I');
        $this->parserService->setSuffix('Interface');

        $content = $this->parserService->getInterfaceContent($fixture);

        $this->assertStringContainsString('someClass: ISomeClassInterface;', $content);
    }

    public function testGetType()
    {
        $reflectionProperty = new ReflectionProperty(AttributeClass::class, "someInterface");
        $type = $this->parserService->getTypeScriptType($reflectionProperty);
        $this->assertStringContainsString('ClassImplementingInterface1|ClassImplementingInterface2', $type->getType());
    }

    public function testShouldNotBreakWhenTryingToParseATrait()
    {
        $fixture = __DIR__ . '/../Fixtures/SomeTrait.php';
        $content = $this->parserService->getInterfaceContent($fixture);

        $this->assertNull($content);
    }

    public function testRespectsTypeScriptTypeAnnotation()
    {
        $fixture = $this->getDefaultFixtureFile();
        $content = $this->parserService->getInterfaceContent($fixture);

        $this->assertStringContainsString('someInterface: ClassImplementingInterface1|ClassImplementingInterface2;', $content);
        $this->assertStringContainsString('someInterfaceArray: ClassImplementingInterface1[]|ClassImplementingInterface2[];', $content);
    }

    public function testContainsPrefixBeforeInterfaceName()
    {
        $fixture = $this->getDefaultFixtureFile();
        $this->parserService->setPrefix('I');
        $content = $this->parserService->getInterfaceContent($fixture);

        $this->assertStringContainsString('interface IAttributeClass', $content);
    }

    public function testSupportsDateTime()
    {
        $fixture = $this->getDefaultFixtureFile();
        $this->parserService->setPrefix('I');
        $content = $this->parserService->getInterfaceContent($fixture);

        $this->assertStringContainsString('dateTime: string', $content);
        $this->assertStringContainsString('dateTime2: string', $content);
    }

    public function testContainsSuffixBeforeInterfaceName()
    {
        $fixture = $this->getDefaultFixtureFile();
        $this->parserService->setSuffix('Interface');
        $content = $this->parserService->getInterfaceContent($fixture);

        $this->assertStringContainsString('interface AttributeClassInterface', $content);
    }

    public function testSupportsNullTypes()
    {
        $fixture = $this->getDefaultFixtureFile();
        $this->parserService->setPrefix('I');
        $this->parserService->setIncludeTypeNullable(true);
        $content = $this->parserService->getInterfaceContent($fixture);

        $this->assertStringContainsString('middleName: string|null', $content);
    }

    public function testSupportsNullTypesDisabled()
    {
        $fixture = $this->getDefaultFixtureFile();
        $this->parserService->setPrefix('I');
        $this->parserService->setIncludeTypeNullable(false);
        $content = $this->parserService->getInterfaceContent($fixture);

        $this->assertStringNotContainsString('middleName: string|null', $content);
    }

    private function loadFixture(): ?string
    {
        $fixture = $this->getDefaultFixtureFile();
        return $this->parserService->getInterfaceContent($fixture);
    }

    private function getDefaultFixtureFile(): string
    {
        return __DIR__ . '/../Fixtures/AttributeClass.php';
    }
}
