<?php

namespace Paneon\PhpToTypeScript\Tests\Services;

use Paneon\PhpToTypeScript\Tests\AbstractTestCase;

class ParserServiceTypeExportTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function defaultOutputIsInterface()
    {
        $content = $this->loadFixture();

        $this->assertStringContainsString('interface Person {', $content);
        $this->assertStringNotContainsString('export', $content);
        $this->assertStringNotContainsString('type Person', $content);
    }

    /**
     * @test
     */
    public function defaultFileExtensionIsDts()
    {
        $fixture = $this->getDefaultFixtureFile();
        $outputFileName = $this->parserService->getOutputFileName($fixture);

        $this->assertStringEndsWith('.d.ts', $outputFileName);
        $this->assertEquals('Person.d.ts', $outputFileName);
    }

    /**
     * @test
     */
    public function canEnableExport()
    {
        $this->parserService->setExport(true);
        $content = $this->loadFixture();

        $this->assertStringContainsString('export interface Person {', $content);
    }

    /**
     * @test
     */
    public function canUseTypeInsteadOfInterface()
    {
        $this->parserService->setUseType(true);
        $content = $this->loadFixture();

        $this->assertStringContainsString('type Person = {', $content);
        $this->assertStringNotContainsString('interface Person', $content);
        $this->assertStringContainsString('};', $content);
    }

    /**
     * @test
     */
    public function typeModeUsesRegularTsExtension()
    {
        $this->parserService->setUseType(true);
        $fixture = $this->getDefaultFixtureFile();
        $outputFileName = $this->parserService->getOutputFileName($fixture);

        $this->assertStringEndsWith('.ts', $outputFileName);
        $this->assertStringNotContainsString('.d.ts', $outputFileName);
        $this->assertEquals('Person.ts', $outputFileName);
    }

    /**
     * @test
     */
    public function canEnableBothExportAndUseType()
    {
        $this->parserService->setUseType(true);
        $this->parserService->setExport(true);
        $content = $this->loadFixture();

        $this->assertStringContainsString('export type Person = {', $content);
        $this->assertStringContainsString('};', $content);
    }

    /**
     * @test
     */
    public function prefixAndSuffixWorkWithType()
    {
        $this->parserService->setUseType(true);
        $this->parserService->setExport(true);
        $this->parserService->setPrefix('I');
        $this->parserService->setSuffix('Type');
        $content = $this->loadFixture();

        $this->assertStringContainsString('export type IPersonType = {', $content);
    }

    /**
     * @test
     */
    public function prefixAndSuffixWorkWithExportedInterface()
    {
        $this->parserService->setExport(true);
        $this->parserService->setPrefix('I');
        $this->parserService->setSuffix('Interface');
        $content = $this->loadFixture();

        $this->assertStringContainsString('export interface IPersonInterface {', $content);
    }

    /**
     * @test
     */
    public function fileExtensionReflectsPrefixSuffixWithType()
    {
        $this->parserService->setUseType(true);
        $this->parserService->setPrefix('I');
        $this->parserService->setSuffix('Type');
        $fixture = $this->getDefaultFixtureFile();
        $outputFileName = $this->parserService->getOutputFileName($fixture);

        $this->assertEquals('IPersonType.ts', $outputFileName);
    }

    /**
     * @test
     */
    public function fileExtensionReflectsPrefixSuffixWithInterface()
    {
        $this->parserService->setPrefix('I');
        $this->parserService->setSuffix('Interface');
        $fixture = $this->getDefaultFixtureFile();
        $outputFileName = $this->parserService->getOutputFileName($fixture);

        $this->assertEquals('IPersonInterface.d.ts', $outputFileName);
    }

    private function loadFixture(): string
    {
        $fixture = $this->getDefaultFixtureFile();
        return $this->parserService->getInterfaceContent($fixture);
    }

    private function getDefaultFixtureFile(): string
    {
        return __DIR__ . '/../Fixtures/Person.php';
    }
}
