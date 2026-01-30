<?php

declare(strict_types=1);

namespace Paneon\PhpToTypeScriptBundle\Tests\Command;

use Paneon\PhpToTypeScript\Parser\PhpDocParser;
use Paneon\PhpToTypeScript\Services\ParserService;
use Paneon\PhpToTypeScriptBundle\Command\GenerateCommand;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

class GenerateCommandTest extends TestCase
{
    private string $outputDir;
    private Filesystem $fs;
    private ParserService $parserService;

    protected function setUp(): void
    {
        $this->outputDir = sys_get_temp_dir() . '/php-to-typescript-test-' . uniqid();
        $this->fs = new Filesystem();
        $this->fs->mkdir($this->outputDir);

        $this->parserService = new ParserService(
            new NullLogger(),
            new PhpDocParser()
        );
    }

    protected function tearDown(): void
    {
        if ($this->fs->exists($this->outputDir)) {
            $this->fs->remove($this->outputDir);
        }
    }

    public function testMultiFileModeGeneratesIndividualFiles(): void
    {
        $inputDir = __DIR__ . '/../Fixtures/';

        $command = new GenerateCommand(
            $this->parserService,
            $this->fs,
            [],
            '',
            '',
            2,
            false,
            $inputDir,
            $this->outputDir,
            [],
            false,
            false,
            false,
            false,
            'types.ts'
        );

        $application = new Application();
        $application->add($command);

        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        // Verify individual files were created
        $this->assertFileExists($this->outputDir . '/SampleClass.d.ts');
        $this->assertFileExists($this->outputDir . '/SampleEnum.d.ts');
        $this->assertFileExists($this->outputDir . '/Address.d.ts');
        $this->assertFileExists($this->outputDir . '/User.d.ts');
        $this->assertFileExists($this->outputDir . '/Nested/Company.d.ts');

        // Verify single file was not created
        $this->assertFileDoesNotExist($this->outputDir . '/types.ts');
    }

    public function testSingleFileModeGeneratesOneFile(): void
    {
        $inputDir = __DIR__ . '/../Fixtures/';

        $command = new GenerateCommand(
            $this->parserService,
            $this->fs,
            [],
            '',
            '',
            2,
            false,
            $inputDir,
            $this->outputDir,
            [],
            false,
            true, // export
            false,
            true, // singleFileMode
            'all-types.ts'
        );

        $application = new Application();
        $application->add($command);

        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        // Verify single file was created
        $this->assertFileExists($this->outputDir . '/all-types.ts');

        // Read and verify content contains all types
        $content = file_get_contents($this->outputDir . '/all-types.ts');
        $this->assertStringContainsString('SampleClass', $content);
        $this->assertStringContainsString('SampleEnum', $content);
        $this->assertStringContainsString('Address', $content);
        $this->assertStringContainsString('User', $content);
        $this->assertStringContainsString('Company', $content);

        // Verify individual files were not created
        $this->assertFileDoesNotExist($this->outputDir . '/SampleClass.d.ts');
    }

    public function testSingleFileModeViaCommandLineOption(): void
    {
        $inputDir = __DIR__ . '/../Fixtures/';

        $command = new GenerateCommand(
            $this->parserService,
            $this->fs,
            [],
            '',
            '',
            2,
            false,
            $inputDir,
            $this->outputDir,
            [],
            false,
            false,
            false,
            false, // singleFileMode disabled in config
            'types.ts'
        );

        $application = new Application();
        $application->add($command);

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            '--single-file' => 'bundle.ts',
        ]);

        // Verify single file was created with CLI-specified name
        $this->assertFileExists($this->outputDir . '/bundle.ts');
    }

    public function testSingleFileModeWithDefaultFilename(): void
    {
        $inputDir = __DIR__ . '/../Fixtures/';

        $command = new GenerateCommand(
            $this->parserService,
            $this->fs,
            [],
            '',
            '',
            2,
            false,
            $inputDir,
            $this->outputDir,
            [],
            false,
            false,
            false,
            false,
            'types.ts'
        );

        $application = new Application();
        $application->add($command);

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            '--single-file' => null, // Use default filename
        ]);

        // Verify single file was created with default name
        $this->assertFileExists($this->outputDir . '/types.ts');
    }

    public function testExportKeywordIsAdded(): void
    {
        $inputDir = __DIR__ . '/../Fixtures/';

        $command = new GenerateCommand(
            $this->parserService,
            $this->fs,
            [],
            '',
            '',
            2,
            false,
            $inputDir,
            $this->outputDir,
            [],
            false,
            true, // export
            false,
            false,
            'types.ts'
        );

        $application = new Application();
        $application->add($command);

        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        // Read generated file and verify export keyword
        $content = file_get_contents($this->outputDir . '/SampleClass.d.ts');
        $this->assertStringContainsString('export', $content);
    }

    public function testExportKeywordViaCommandLineOption(): void
    {
        $inputDir = __DIR__ . '/../Fixtures/';

        $command = new GenerateCommand(
            $this->parserService,
            $this->fs,
            [],
            '',
            '',
            2,
            false,
            $inputDir,
            $this->outputDir,
            [],
            false,
            false, // export disabled in config
            false,
            false,
            'types.ts'
        );

        $application = new Application();
        $application->add($command);

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            '--export' => true,
        ]);

        // Read generated file and verify export keyword
        $content = file_get_contents($this->outputDir . '/SampleClass.d.ts');
        $this->assertStringContainsString('export', $content);
    }

    public function testUseTypeGeneratesTsExtension(): void
    {
        $inputDir = __DIR__ . '/../Fixtures/';

        $command = new GenerateCommand(
            $this->parserService,
            $this->fs,
            [],
            '',
            '',
            2,
            false,
            $inputDir,
            $this->outputDir,
            [],
            true, // useType
            false,
            false,
            false,
            'types.ts'
        );

        $application = new Application();
        $application->add($command);

        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        // Verify .ts files were created instead of .d.ts
        $this->assertFileExists($this->outputDir . '/SampleClass.ts');
        $this->assertFileDoesNotExist($this->outputDir . '/SampleClass.d.ts');
    }

    public function testPrefixAndSuffix(): void
    {
        $inputDir = __DIR__ . '/../Fixtures/';

        $command = new GenerateCommand(
            $this->parserService,
            $this->fs,
            [],
            'I',
            'Type',
            2,
            false,
            $inputDir,
            $this->outputDir,
            [],
            false,
            false,
            false,
            false,
            'types.ts'
        );

        $application = new Application();
        $application->add($command);

        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        // Verify files with prefix and suffix were created
        $this->assertFileExists($this->outputDir . '/ISampleClassType.d.ts');
    }

    public function testNestedDirectoryStructureIsPreserved(): void
    {
        $inputDir = __DIR__ . '/../Fixtures/';

        $command = new GenerateCommand(
            $this->parserService,
            $this->fs,
            [],
            '',
            '',
            2,
            false,
            $inputDir,
            $this->outputDir,
            [],
            false,
            false,
            false,
            false,
            'types.ts'
        );

        $application = new Application();
        $application->add($command);

        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        // Verify nested directory structure
        $this->assertFileExists($this->outputDir . '/Nested/Company.d.ts');
    }

    public function testSingleFileModeWithExportHasNoImports(): void
    {
        $inputDir = __DIR__ . '/../Fixtures/';

        $command = new GenerateCommand(
            $this->parserService,
            $this->fs,
            [],
            '',
            '',
            2,
            false,
            $inputDir,
            $this->outputDir,
            [],
            false,
            true, // export
            false,
            true, // singleFileMode
            'all-types.ts'
        );

        $application = new Application();
        $application->add($command);

        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        // Read single file and verify no import statements
        $content = file_get_contents($this->outputDir . '/all-types.ts');
        $this->assertStringNotContainsString('import ', $content);
    }

    public function testCommandOutputShowsProgress(): void
    {
        $inputDir = __DIR__ . '/../Fixtures/';

        $command = new GenerateCommand(
            $this->parserService,
            $this->fs,
            [],
            '',
            '',
            2,
            false,
            $inputDir,
            $this->outputDir,
            [],
            false,
            false,
            false,
            false,
            'types.ts'
        );

        $application = new Application();
        $application->add($command);

        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Processing directory:', $output);
        $this->assertStringContainsString('...done!', $output);
    }

    public function testSingleFileModeOutputShowsProgress(): void
    {
        $inputDir = __DIR__ . '/../Fixtures/';

        $command = new GenerateCommand(
            $this->parserService,
            $this->fs,
            [],
            '',
            '',
            2,
            false,
            $inputDir,
            $this->outputDir,
            [],
            false,
            false,
            false,
            true,
            'types.ts'
        );

        $application = new Application();
        $application->add($command);

        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Generating single file:', $output);
        $this->assertStringContainsString('...done!', $output);
    }
}
