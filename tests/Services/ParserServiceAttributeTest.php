<?php

namespace Paneon\PhpToTypeScript\Tests\Services;

use Paneon\PhpToTypeScript\Tests\AbstractTestCase;
use ReflectionException;

class ParserServiceAttributeTest extends AbstractTestCase
{

    /**
     * @test
     */
    public function triggersOnClassesWithCustomAnnotation()
    {
        $content = $this->loadFixture();

        $this->assertNotNull($content);
    }

    /**
     * @test
     */
    public function doesntContainExcludedProperties()
    {
        $content = $this->loadFixture();

        $this->assertStringNotContainsString('excluded', $content);
    }

    /**
     * @test
     */
    public function containsVirtualProperties()
    {
        $content = $this->loadFixture();

        $this->assertStringContainsString('hasSomeValue: boolean;', $content);
        $this->assertStringContainsString('virtualWithReturnType: number', $content);
    }

    /**
     * @test
     */
    public function addsPrefixAndSuffixToClassInstances()
    {
        $fixture = $this->getDefaultFixtureFile();

        $this->parserService->setPrefix('I');
        $this->parserService->setSuffix('Interface');

        $content = $this->parserService->getInterfaceContent($fixture);

        $this->assertStringContainsString('someClass: ISomeClassInterface;', $content);
    }

    public function testGetTypeScriptType(): void
    {

    }

    /**
     * @test
     */
    public function shouldNotBreakWhenTryingToParseATrait()
    {
        $fixture = __DIR__ . '/../Fixtures/SomeTrait.php';
        $content = $this->parserService->getInterfaceContent($fixture);

        $this->assertNull($content);
    }


    /**
     * @test
     */
    public function respectsTypeScriptTypeAnnotation()
    {
        $fixture = $this->getDefaultFixtureFile();
        $content = $this->parserService->getInterfaceContent($fixture);

        $this->assertStringContainsString('someInterface: ClassImplementingInterface1|ClassImplementingInterface2;', $content);
        $this->assertStringContainsString('someInterfaceArray: ClassImplementingInterface1[]|ClassImplementingInterface2[];', $content);
    }

    /**
     * @test
     */
    public function containsPrefixBeforeInterfaceName()
    {
        $fixture = $this->getDefaultFixtureFile();
        $this->parserService->setPrefix('I');
        $content = $this->parserService->getInterfaceContent($fixture);

        $this->assertStringContainsString('interface IPerson', $content);
    }

    /**
     * @test
     */
    public function supportsDateTime()
    {
        $fixture = $this->getDefaultFixtureFile();
        $this->parserService->setPrefix('I');
        $content = $this->parserService->getInterfaceContent($fixture);

        $this->assertStringContainsString('dateTime: string', $content);
        $this->assertStringContainsString('dateTime2: string', $content);
    }

    /**
     * @test
     */
    public function containsSuffixBeforeInterfaceName()
    {
        $fixture = $this->getDefaultFixtureFile();
        $this->parserService->setSuffix('Interface');
        $content = $this->parserService->getInterfaceContent($fixture);

        $this->assertStringContainsString('interface PersonInterface', $content);
    }

    /**
     * @test
     */
    public function supportsNullTypes()
    {
        $fixture = $this->getDefaultFixtureFile();
        $this->parserService->setPrefix('I');
        $this->parserService->setIncludeTypeNullable(true);
        $content = $this->parserService->getInterfaceContent($fixture);

        $this->assertStringContainsString('middleName: string|null', $content);
    }

    /**
     * @test
     */
    public function supportsNullTypesDisabled()
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
