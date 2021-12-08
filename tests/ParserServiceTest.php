<?php

namespace Paneon\PhpToTypeScript\Tests;

use ReflectionException;

class ParserServiceTest extends AbstractTestCase
{

    /**
     * @test
     */
    public function triggersOnClassesWithCustomAnnotation()
    {
        $fixture = __DIR__ . '/fixtures/Person.php';
        $content = $this->parserService->getInterfaceContent($fixture);

        $this->assertNotNull($content);
    }

    /**
     * @test
     */
    public function doesntContainExcludedProperties()
    {
        $fixture = __DIR__ . '/fixtures/Person.php';
        $content = $this->parserService->getInterfaceContent($fixture);

        $this->assertStringNotContainsString('excluded', $content);
    }

    /**
     * @test
     */
    public function containsVirtualProperties()
    {
        $fixture = __DIR__ . '/fixtures/Person.php';
        $content = $this->parserService->getInterfaceContent($fixture);

        $this->assertStringContainsString('hasSomeValue: boolean;', $content);
        $this->assertStringContainsString('virtualWithReturnType: number', $content);
    }

    /**
     * @test
     */
    public function addsPrefixAndSuffixToClassInstances()
    {
        $fixture = __DIR__ . '/fixtures/Person.php';

        $this->parserService->setPrefix('I');
        $this->parserService->setSuffix('Interface');

        $content = $this->parserService->getInterfaceContent($fixture);

        $this->assertStringContainsString('someClass: ISomeClassInterface;', $content);
    }

    /**
     * @test
     */
    public function shouldNotBreakWhenTryingToParseATrait()
    {
        $fixture = __DIR__ . '/fixtures/SomeTrait.php';
        $content = $this->parserService->getInterfaceContent($fixture);

        $this->assertNull($content);
    }

    /**
     * @test
     */
    public function convertsMixedToArray()
    {
        $fixture = __DIR__ . '/fixtures/Person.php';
        $content = $this->parserService->getInterfaceContent($fixture);

        $this->assertStringContainsString('mixed: any;', $content);
        $this->assertStringContainsString('mixedArray: any[];', $content);
    }

    /**
     * @test
     */
    public function respectsTypeScriptTypeAnnotation()
    {
        $fixture = __DIR__ . '/fixtures/Person.php';
        $content = $this->parserService->getInterfaceContent($fixture);

        $this->assertStringContainsString('someInterface: ClassImplementingInterface1|ClassImplementingInterface2;', $content);
        $this->assertStringContainsString('someInterfaceArray: ClassImplementingInterface1[]|ClassImplementingInterface2[];', $content);
    }

    /**
     * @test
     */
    public function containsPrefixBeforeInterfaceName()
    {
        $fixture = __DIR__ . '/fixtures/Person.php';
        $this->parserService->setPrefix('I');
        $content = $this->parserService->getInterfaceContent($fixture);

        $this->assertStringContainsString('interface IPerson', $content);
    }

    /**
     * @test
     */
    public function supportsDateTime()
    {
        $fixture = __DIR__ . '/fixtures/Person.php';
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
        $fixture = __DIR__ . '/fixtures/Person.php';
        $this->parserService->setSuffix('Interface');
        $content = $this->parserService->getInterfaceContent($fixture);

        $this->assertStringContainsString('interface PersonInterface', $content);
    }

    /**
     * @test
     */
    public function supportsNullTypes()
    {
        $fixture = __DIR__ . '/fixtures/Person.php';
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
        $fixture = __DIR__ . '/fixtures/Person.php';
        $this->parserService->setPrefix('I');
        $this->parserService->setIncludeTypeNullable(false);
        $content = $this->parserService->getInterfaceContent($fixture);

        $this->assertStringNotContainsString('middleName: string|null', $content);
    }
}
