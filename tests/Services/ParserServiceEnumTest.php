<?php

namespace Paneon\PhpToTypeScript\Tests\Services;

use Paneon\PhpToTypeScript\Tests\AbstractTestCase;

class ParserServiceEnumTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function parsesStringBackedEnum()
    {
        $fixture = __DIR__ . '/../Fixtures/SuitEnum.php';
        $content = $this->parserService->getEnumContent($fixture);

        $this->assertNotNull($content);
        $this->assertStringContainsString('enum SuitEnum {', $content);
        $this->assertStringContainsString("Hearts = 'hearts',", $content);
        $this->assertStringContainsString("Diamonds = 'diamonds',", $content);
        $this->assertStringContainsString("Clubs = 'clubs',", $content);
        $this->assertStringContainsString("Spades = 'spades',", $content);
    }

    /**
     * @test
     */
    public function parsesIntBackedEnum()
    {
        $fixture = __DIR__ . '/../Fixtures/StatusEnum.php';
        $content = $this->parserService->getEnumContent($fixture);

        $this->assertNotNull($content);
        $this->assertStringContainsString('enum StatusEnum {', $content);
        $this->assertStringContainsString('Pending = 0,', $content);
        $this->assertStringContainsString('Active = 1,', $content);
        $this->assertStringContainsString('Completed = 2,', $content);
        $this->assertStringContainsString('Cancelled = 3,', $content);
    }

    /**
     * @test
     */
    public function parsesUnitEnum()
    {
        $fixture = __DIR__ . '/../Fixtures/ColorEnum.php';
        $content = $this->parserService->getEnumContent($fixture);

        $this->assertNotNull($content);
        $this->assertStringContainsString('enum ColorEnum {', $content);
        $this->assertStringContainsString('Red,', $content);
        $this->assertStringContainsString('Green,', $content);
        $this->assertStringContainsString('Blue,', $content);
        // Unit enums should not have values assigned
        $this->assertStringNotContainsString('Red =', $content);
    }

    /**
     * @test
     */
    public function parsesEnumAsUnionType()
    {
        $fixture = __DIR__ . '/../Fixtures/SuitUnionEnum.php';
        $content = $this->parserService->getEnumContent($fixture);

        $this->assertNotNull($content);
        $this->assertStringContainsString("type SuitUnionEnum = 'hearts' | 'diamonds' | 'clubs' | 'spades';", $content);
        $this->assertStringNotContainsString('enum SuitUnionEnum', $content);
    }

    /**
     * @test
     */
    public function addsExportKeyword()
    {
        $fixture = __DIR__ . '/../Fixtures/SuitEnum.php';
        $this->parserService->setExport(true);
        $content = $this->parserService->getEnumContent($fixture);

        $this->assertNotNull($content);
        $this->assertStringContainsString('export enum SuitEnum {', $content);
    }

    /**
     * @test
     */
    public function addsExportKeywordToUnion()
    {
        $fixture = __DIR__ . '/../Fixtures/SuitUnionEnum.php';
        $this->parserService->setExport(true);
        $content = $this->parserService->getEnumContent($fixture);

        $this->assertNotNull($content);
        $this->assertStringContainsString('export type SuitUnionEnum =', $content);
    }

    /**
     * @test
     */
    public function addsPrefixAndSuffixToEnum()
    {
        $fixture = __DIR__ . '/../Fixtures/SuitEnum.php';
        $this->parserService->setPrefix('E');
        $this->parserService->setSuffix('Type');
        $content = $this->parserService->getEnumContent($fixture);

        $this->assertNotNull($content);
        $this->assertStringContainsString('enum ESuitEnumType {', $content);
    }

    /**
     * @test
     */
    public function returnsNullForNonEnumFile()
    {
        $fixture = __DIR__ . '/../Fixtures/Person.php';
        $content = $this->parserService->getEnumContent($fixture);

        $this->assertNull($content);
    }

    /**
     * @test
     */
    public function globalUnionTypeSetting()
    {
        $fixture = __DIR__ . '/../Fixtures/SuitEnum.php';
        $this->parserService->setUseEnumUnionType(true);
        $content = $this->parserService->getEnumContent($fixture);

        // Note: attribute asUnion=false should override global setting
        // SuitEnum doesn't have asUnion: true, so it would use global setting
        $this->assertNotNull($content);
        $this->assertStringContainsString("type SuitEnum = 'hearts' | 'diamonds' | 'clubs' | 'spades';", $content);
    }
}
