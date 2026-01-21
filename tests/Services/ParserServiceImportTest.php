<?php

namespace Paneon\PhpToTypeScript\Tests\Services;

use Paneon\PhpToTypeScript\Model\SourceFile;
use Paneon\PhpToTypeScript\Model\SourceFileCollection;
use Paneon\PhpToTypeScript\Tests\AbstractTestCase;

class ParserServiceImportTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function generatesImportStatements()
    {
        $sourceFiles = new SourceFileCollection();
        $sourceFiles->add(new SourceFile(
            'Paneon\PhpToTypeScript\Tests\Fixtures\CartResponseDTO',
            __DIR__ . '/../Fixtures/CartResponseDTO.php',
            '/tmp/types'
        ));
        $sourceFiles->add(new SourceFile(
            'Paneon\PhpToTypeScript\Tests\Fixtures\ProductDetailDTO',
            __DIR__ . '/../Fixtures/ProductDetailDTO.php',
            '/tmp/types'
        ));

        $this->parserService
            ->setExport(true)
            ->setSourceFiles($sourceFiles)
            ->setCurrentTargetDirectory('/tmp/types');

        $content = $this->parserService->getInterfaceContent(
            __DIR__ . '/../Fixtures/CartResponseDTO.php'
        );

        $this->assertNotNull($content);
        $this->assertStringContainsString("import { ProductDetailDTO } from './ProductDetailDTO';", $content);
        $this->assertStringContainsString('export interface CartResponseDTO', $content);
        $this->assertStringContainsString('items: ProductDetailDTO[];', $content);
    }

    /**
     * @test
     */
    public function noImportsInSingleFileMode()
    {
        $sourceFiles = new SourceFileCollection();
        $sourceFiles->add(new SourceFile(
            'Paneon\PhpToTypeScript\Tests\Fixtures\CartResponseDTO',
            __DIR__ . '/../Fixtures/CartResponseDTO.php',
            '/tmp/types'
        ));
        $sourceFiles->add(new SourceFile(
            'Paneon\PhpToTypeScript\Tests\Fixtures\ProductDetailDTO',
            __DIR__ . '/../Fixtures/ProductDetailDTO.php',
            '/tmp/types'
        ));

        $this->parserService
            ->setExport(true)
            ->setSingleFileMode(true)
            ->setSourceFiles($sourceFiles)
            ->setCurrentTargetDirectory('/tmp/types');

        $content = $this->parserService->getInterfaceContent(
            __DIR__ . '/../Fixtures/CartResponseDTO.php'
        );

        $this->assertNotNull($content);
        $this->assertStringNotContainsString('import', $content);
        $this->assertStringContainsString('export interface CartResponseDTO', $content);
    }

    /**
     * @test
     */
    public function importsWithPrefixAndSuffix()
    {
        $sourceFiles = new SourceFileCollection();
        $sourceFiles->add(new SourceFile(
            'Paneon\PhpToTypeScript\Tests\Fixtures\CartResponseDTO',
            __DIR__ . '/../Fixtures/CartResponseDTO.php',
            '/tmp/types'
        ));
        $sourceFiles->add(new SourceFile(
            'Paneon\PhpToTypeScript\Tests\Fixtures\ProductDetailDTO',
            __DIR__ . '/../Fixtures/ProductDetailDTO.php',
            '/tmp/types'
        ));

        $this->parserService
            ->setExport(true)
            ->setPrefix('I')
            ->setSuffix('Interface')
            ->setSourceFiles($sourceFiles)
            ->setCurrentTargetDirectory('/tmp/types');

        $content = $this->parserService->getInterfaceContent(
            __DIR__ . '/../Fixtures/CartResponseDTO.php'
        );

        $this->assertNotNull($content);
        $this->assertStringContainsString("import { IProductDetailDTOInterface } from './IProductDetailDTOInterface';", $content);
        $this->assertStringContainsString('export interface ICartResponseDTOInterface', $content);
    }

    /**
     * @test
     */
    public function importsFromDifferentDirectory()
    {
        $sourceFiles = new SourceFileCollection();
        $sourceFiles->add(new SourceFile(
            'Paneon\PhpToTypeScript\Tests\Fixtures\CartResponseDTO',
            __DIR__ . '/../Fixtures/CartResponseDTO.php',
            '/tmp/types/cart'
        ));
        $sourceFiles->add(new SourceFile(
            'Paneon\PhpToTypeScript\Tests\Fixtures\ProductDetailDTO',
            __DIR__ . '/../Fixtures/ProductDetailDTO.php',
            '/tmp/types/products'
        ));

        $this->parserService
            ->setExport(true)
            ->setSourceFiles($sourceFiles)
            ->setCurrentTargetDirectory('/tmp/types/cart');

        $content = $this->parserService->getInterfaceContent(
            __DIR__ . '/../Fixtures/CartResponseDTO.php'
        );

        $this->assertNotNull($content);
        $this->assertStringContainsString("import { ProductDetailDTO } from '../products/ProductDetailDTO';", $content);
    }

    /**
     * @test
     */
    public function noImportForUnknownTypes()
    {
        // Only register CartResponseDTO, not ProductDetailDTO
        $sourceFiles = new SourceFileCollection();
        $sourceFiles->add(new SourceFile(
            'Paneon\PhpToTypeScript\Tests\Fixtures\CartResponseDTO',
            __DIR__ . '/../Fixtures/CartResponseDTO.php',
            '/tmp/types'
        ));

        $this->parserService
            ->setExport(true)
            ->setSourceFiles($sourceFiles)
            ->setCurrentTargetDirectory('/tmp/types');

        $content = $this->parserService->getInterfaceContent(
            __DIR__ . '/../Fixtures/CartResponseDTO.php'
        );

        $this->assertNotNull($content);
        // ProductDetailDTO is not in collection, so no import
        $this->assertStringNotContainsString('import', $content);
        // But the type should still be referenced
        $this->assertStringContainsString('items: ProductDetailDTO[];', $content);
    }
}
