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

    public function testInterfacesMapSyntax(): void
    {
        $configuration = new Configuration();
        $processor = new Processor();
        $config = $processor->processConfiguration($configuration, [
            [
                'interfaces' => [
                    'vendor/foo/src/Dto.php' => [
                        'output' => 'external/foo/',
                    ],
                    'vendor/bar/src/Model.php' => [
                        'output' => 'external/bar/',
                    ],
                ],
            ]
        ]);

        $this->assertCount(2, $config['interfaces']);
        $this->assertSame('vendor/foo/src/Dto.php', $config['interfaces'][0]['path']);
        $this->assertSame('external/foo/', $config['interfaces'][0]['output']);
        $this->assertSame('vendor/bar/src/Model.php', $config['interfaces'][1]['path']);
        $this->assertSame('external/bar/', $config['interfaces'][1]['output']);
    }

    public function testInterfacesListSyntax(): void
    {
        $configuration = new Configuration();
        $processor = new Processor();
        $config = $processor->processConfiguration($configuration, [
            [
                'interfaces' => [
                    [
                        'path' => 'vendor/foo/src/Dto.php',
                        'output' => 'external/foo/',
                    ],
                    [
                        'path' => 'vendor/bar/src/Model.php',
                        'output' => 'external/bar/',
                    ],
                ],
            ]
        ]);

        $this->assertCount(2, $config['interfaces']);
        $this->assertSame('vendor/foo/src/Dto.php', $config['interfaces'][0]['path']);
        $this->assertSame('external/foo/', $config['interfaces'][0]['output']);
        $this->assertSame('vendor/bar/src/Model.php', $config['interfaces'][1]['path']);
        $this->assertSame('external/bar/', $config['interfaces'][1]['output']);
    }

    public function testDirectoriesMapSyntax(): void
    {
        $configuration = new Configuration();
        $processor = new Processor();
        $config = $processor->processConfiguration($configuration, [
            [
                'directories' => [
                    'vendor/foo/src/' => [
                        'output' => 'external/foo/',
                        'requireAnnotation' => true,
                    ],
                    'vendor/bar/src/' => [
                        'output' => 'external/bar/',
                        'requireAnnotation' => false,
                    ],
                ],
            ]
        ]);

        $this->assertCount(2, $config['directories']);
        $this->assertSame('vendor/foo/src/', $config['directories'][0]['path']);
        $this->assertSame('external/foo/', $config['directories'][0]['output']);
        $this->assertTrue($config['directories'][0]['requireAnnotation']);
        $this->assertSame('vendor/bar/src/', $config['directories'][1]['path']);
        $this->assertSame('external/bar/', $config['directories'][1]['output']);
        $this->assertFalse($config['directories'][1]['requireAnnotation']);
    }

    public function testDirectoriesListSyntax(): void
    {
        $configuration = new Configuration();
        $processor = new Processor();
        $config = $processor->processConfiguration($configuration, [
            [
                'directories' => [
                    [
                        'path' => 'vendor/foo/src/',
                        'output' => 'external/foo/',
                        'requireAnnotation' => true,
                    ],
                    [
                        'path' => 'vendor/bar/src/',
                        'output' => 'external/bar/',
                        'requireAnnotation' => false,
                    ],
                ],
            ]
        ]);

        $this->assertCount(2, $config['directories']);
        $this->assertSame('vendor/foo/src/', $config['directories'][0]['path']);
        $this->assertSame('external/foo/', $config['directories'][0]['output']);
        $this->assertTrue($config['directories'][0]['requireAnnotation']);
        $this->assertSame('vendor/bar/src/', $config['directories'][1]['path']);
        $this->assertSame('external/bar/', $config['directories'][1]['output']);
        $this->assertFalse($config['directories'][1]['requireAnnotation']);
    }

    public function testDirectoriesListSyntaxWithDefaultRequireAnnotation(): void
    {
        $configuration = new Configuration();
        $processor = new Processor();
        $config = $processor->processConfiguration($configuration, [
            [
                'directories' => [
                    [
                        'path' => 'vendor/foo/src/',
                        'output' => 'external/foo/',
                    ],
                ],
            ]
        ]);

        $this->assertCount(1, $config['directories']);
        $this->assertSame('vendor/foo/src/', $config['directories'][0]['path']);
        $this->assertSame('external/foo/', $config['directories'][0]['output']);
        $this->assertFalse($config['directories'][0]['requireAnnotation']);
    }
}
