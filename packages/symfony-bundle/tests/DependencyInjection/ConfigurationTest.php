<?php

declare(strict_types=1);

namespace Paneon\PhpToTypeScriptBundle\Tests\DependencyInjection;

use Paneon\PhpToTypeScriptBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends TestCase
{
    public function testDefaultConfiguration(): void
    {
        $configuration = new Configuration();
        $processor = new Processor();
        $config = $processor->processConfiguration($configuration, []);

        $this->assertSame(2, $config['indentation']);
        $this->assertSame('src/', $config['inputDirectory']);
        $this->assertSame('assets/js/interfaces/', $config['outputDirectory']);
        $this->assertSame('', $config['prefix']);
        $this->assertSame('', $config['suffix']);
        $this->assertFalse($config['nullable']);
        $this->assertFalse($config['useType']);
        $this->assertFalse($config['export']);
        $this->assertFalse($config['useEnumUnionType']);
        $this->assertFalse($config['singleFileMode']);
        $this->assertSame('types.ts', $config['singleFileOutput']);
    }

    public function testCustomConfiguration(): void
    {
        $configuration = new Configuration();
        $processor = new Processor();
        $config = $processor->processConfiguration($configuration, [
            [
                'indentation' => 4,
                'inputDirectory' => 'app/',
                'outputDirectory' => 'public/js/types/',
                'prefix' => 'I',
                'suffix' => 'Type',
                'nullable' => true,
                'useType' => true,
                'export' => true,
                'useEnumUnionType' => true,
                'singleFileMode' => true,
                'singleFileOutput' => 'all-types.ts',
            ]
        ]);

        $this->assertSame(4, $config['indentation']);
        $this->assertSame('app/', $config['inputDirectory']);
        $this->assertSame('public/js/types/', $config['outputDirectory']);
        $this->assertSame('I', $config['prefix']);
        $this->assertSame('Type', $config['suffix']);
        $this->assertTrue($config['nullable']);
        $this->assertTrue($config['useType']);
        $this->assertTrue($config['export']);
        $this->assertTrue($config['useEnumUnionType']);
        $this->assertTrue($config['singleFileMode']);
        $this->assertSame('all-types.ts', $config['singleFileOutput']);
    }

    public function testPartialConfiguration(): void
    {
        $configuration = new Configuration();
        $processor = new Processor();
        $config = $processor->processConfiguration($configuration, [
            [
                'useType' => true,
                'export' => true,
            ]
        ]);

        // New options should be set
        $this->assertTrue($config['useType']);
        $this->assertTrue($config['export']);

        // Other options should keep defaults
        $this->assertFalse($config['useEnumUnionType']);
        $this->assertFalse($config['nullable']);
        $this->assertSame(2, $config['indentation']);
        $this->assertFalse($config['singleFileMode']);
        $this->assertSame('types.ts', $config['singleFileOutput']);
    }

    public function testSingleFileModeConfiguration(): void
    {
        $configuration = new Configuration();
        $processor = new Processor();
        $config = $processor->processConfiguration($configuration, [
            [
                'singleFileMode' => true,
            ]
        ]);

        $this->assertTrue($config['singleFileMode']);
        $this->assertSame('types.ts', $config['singleFileOutput']);
    }

    public function testSingleFileOutputWithoutMode(): void
    {
        $configuration = new Configuration();
        $processor = new Processor();
        $config = $processor->processConfiguration($configuration, [
            [
                'singleFileOutput' => 'bundle.ts',
            ]
        ]);

        // singleFileMode should still be false, but output filename should be set
        $this->assertFalse($config['singleFileMode']);
        $this->assertSame('bundle.ts', $config['singleFileOutput']);
    }
}
