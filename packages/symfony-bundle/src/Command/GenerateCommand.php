<?php

namespace Paneon\PhpToTypeScriptBundle\Command;

use Paneon\PhpToTypeScript\Model\SourceFileCollection;
use Paneon\PhpToTypeScript\Services\ParserService;
use PhpParser\Node\Stmt\Namespace_;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

#[AsCommand(
    name: 'typescript:generate',
    description: 'Generate TypeScript interfaces from PHP classes in a directory'
)]
class GenerateCommand extends Command
{
    protected static $defaultName = 'typescript:generate';

    public function __construct(
        protected ParserService $parserService,
        protected Filesystem $fs,
        protected ?array $additionalFiles,
        ?string $prefix,
        ?string $suffix,
        ?int $indent,
        protected bool $nullable,
        protected string $inputDirectory,
        protected string $outputDirectory,
        protected array $additionalDirectories,
        protected bool $useType = false,
        protected bool $export = false,
        protected bool $useEnumUnionType = false,
        protected bool $singleFileMode = false,
        protected string $singleFileOutput = 'types.ts',
    ) {
        parent::__construct();

        if ($prefix) {
            $this->parserService->setPrefix($prefix);
        }
        if ($suffix) {
            $this->parserService->setSuffix($suffix);
        }
        if ($indent) {
            $this->parserService->setIndent($indent);
        }
        if ($nullable) {
            $this->parserService->setIncludeTypeNullable($nullable);
        }
        if ($useType) {
            $this->parserService->setUseType($useType);
        }
        if ($export) {
            $this->parserService->setExport($export);
        }
        if ($useEnumUnionType) {
            $this->parserService->setUseEnumUnionType($useEnumUnionType);
        }
    }

    protected function configure()
    {
        $this
            ->setName('typescript:generate')
            ->setDescription('Generate TypeScript interfaces from PHP classes in a directory')
            ->addOption(
                'nullable',
                null,
                InputOption::VALUE_OPTIONAL,
                'Whether or not to use Null-aware types for TS v2 and later',
                false
            )
            ->addOption(
                'use-type',
                null,
                InputOption::VALUE_NONE,
                'Generate "type" instead of "interface" (outputs .ts instead of .d.ts)'
            )
            ->addOption(
                'export',
                null,
                InputOption::VALUE_NONE,
                'Add "export" keyword before declarations'
            )
            ->addOption(
                'enum-union-type',
                null,
                InputOption::VALUE_NONE,
                'Output enums as string literal union types'
            )
            ->addOption(
                'single-file',
                null,
                InputOption::VALUE_OPTIONAL,
                'Output all types to a single file (optionally specify filename)',
                false
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $includeTypeNullable = $input->getOption('nullable') !== false;
        $useType = $input->getOption('use-type');
        $export = $input->getOption('export');
        $enumUnionType = $input->getOption('enum-union-type');
        $singleFileOption = $input->getOption('single-file');

        // Determine single file mode and output filename
        $singleFileMode = $this->singleFileMode;
        $singleFileOutput = $this->singleFileOutput;
        if ($singleFileOption !== false) {
            $singleFileMode = true;
            if ($singleFileOption !== null && is_string($singleFileOption)) {
                $singleFileOutput = $singleFileOption;
            }
        }

        if ($includeTypeNullable) {
            $this->parserService->setIncludeTypeNullable($includeTypeNullable);
        }
        if ($useType) {
            $this->parserService->setUseType(true);
        }
        if ($export) {
            $this->parserService->setExport(true);
        }
        if ($enumUnionType) {
            $this->parserService->setUseEnumUnionType(true);
        }

        // Set single file mode on parser (disables import generation)
        $this->parserService->setSingleFileMode($singleFileMode);

        // Collect all source files for import resolution (when using exports)
        $sourceFileCollection = $this->collectSourceFiles();
        $this->parserService->setSourceFiles($sourceFileCollection);

        if ($singleFileMode) {
            $this->executeInSingleFileMode($output, $singleFileOutput, $sourceFileCollection);
        } else {
            $this->executeInMultiFileMode($output, $sourceFileCollection);
        }

        $output->writeln(PHP_EOL . '...done!');

        return 0;
    }

    /**
     * Collects all source files that will be processed into a SourceFileCollection.
     * This is used for resolving import paths when generating TypeScript with exports.
     */
    protected function collectSourceFiles(): SourceFileCollection
    {
        $collection = new SourceFileCollection();

        // Collect from main input directory
        $this->collectFromDirectory(
            $this->inputDirectory,
            $this->outputDirectory,
            true,
            $collection
        );

        // Collect from additional directories
        if ($this->additionalDirectories) {
            foreach ($this->additionalDirectories as $fromDir => $configArray) {
                $this->collectFromDirectory(
                    $fromDir,
                    $this->outputDirectory . $configArray['output'],
                    $configArray['requireAnnotation'],
                    $collection
                );
            }
        }

        // Collect additional files
        foreach ($this->additionalFiles as $additionalFile => $configArray) {
            $additionalFileOutputDir = $this->outputDirectory . $configArray['output'];
            if ($additionalFileOutputDir[-1] !== DIRECTORY_SEPARATOR) {
                $additionalFileOutputDir .= DIRECTORY_SEPARATOR;
            }
            $this->collectFile($additionalFile, $additionalFileOutputDir, false, $collection);
        }

        return $collection;
    }

    /**
     * Collect files from a directory into the source file collection.
     */
    protected function collectFromDirectory(
        string $inputDir,
        string $outputDir,
        bool $requireAnnotation,
        SourceFileCollection $collection
    ): void {
        // Normalize input directory to real path for comparison with Finder results
        $inputDir = realpath($inputDir);
        if ($inputDir === false) {
            return;
        }

        if ($inputDir[-1] !== DIRECTORY_SEPARATOR) {
            $inputDir .= DIRECTORY_SEPARATOR;
        }

        if ($outputDir[-1] !== DIRECTORY_SEPARATOR) {
            $outputDir .= DIRECTORY_SEPARATOR;
        }

        $files = $this->getPhpFiles($inputDir);

        foreach ($files as $sourceFileName) {
            $diffStart = strpos($sourceFileName, $inputDir) + strlen($inputDir);
            $diffEnd = strrpos($sourceFileName, DIRECTORY_SEPARATOR);
            $directoryDiff = substr($sourceFileName, $diffStart, $diffEnd - $diffStart + 1);
            $targetDirectory = $outputDir . $directoryDiff;

            $this->collectFile($sourceFileName, $targetDirectory, $requireAnnotation, $collection);
        }
    }

    /**
     * Collect a single file into the source file collection.
     */
    protected function collectFile(
        string $sourceFileName,
        string $targetDirectory,
        bool $requireAnnotation,
        SourceFileCollection $collection
    ): void {
        // Try to get the class name from the file
        $stmts = $this->parserService->getStatements($sourceFileName);
        $className = $this->getFullyQualifiedClassName($stmts, $sourceFileName);

        if ($className !== null) {
            $collection->addFromArray($className, $sourceFileName, rtrim($targetDirectory, DIRECTORY_SEPARATOR));
        }
    }

    /**
     * Get fully qualified class name from parsed statements.
     */
    private function getFullyQualifiedClassName(array $stmts, string $sourceFileName): ?string
    {
        $pathinfo = pathinfo($sourceFileName);

        foreach ($stmts as $statement) {
            if ($statement instanceof Namespace_) {
                $namespace = $statement->name?->toString();
                if (!empty($namespace)) {
                    return $namespace . '\\' . $pathinfo['filename'];
                }
                return $pathinfo['filename'];
            }
        }

        return null;
    }

    /**
     * Execute in multi-file mode (default behavior with optional import resolution).
     */
    protected function executeInMultiFileMode(OutputInterface $output, SourceFileCollection $sourceFileCollection): void
    {
        // Process project source code
        $this->processDirectory($this->inputDirectory, $this->outputDirectory, true, $output);

        // Process external files
        if ($this->additionalDirectories) {
            foreach ($this->additionalDirectories as $fromDir => $configArray) {
                $this->processDirectory(
                    $fromDir,
                    $this->outputDirectory . $configArray['output'],
                    $configArray['requireAnnotation'],
                    $output
                );
            }
        }

        foreach ($this->additionalFiles as $additionalFile => $configArray) {
            $sourceFileName = $additionalFile;
            $additionalFileOutputDir = $this->outputDirectory . $configArray['output'];

            if ($additionalFileOutputDir[-1] !== DIRECTORY_SEPARATOR) {
                $additionalFileOutputDir .= DIRECTORY_SEPARATOR;
            }

            // Set current target directory for import resolution
            $this->parserService->setCurrentTargetDirectory(rtrim($additionalFileOutputDir, DIRECTORY_SEPARATOR));

            $content = $this->parserService->getContent($sourceFileName, false);

            if ($content) {
                $targetFile = $additionalFileOutputDir . $this->parserService->getOutputFileName($sourceFileName);
                $this->fs->dumpFile($targetFile, $content);

                $output->writeln('- ' . $sourceFileName . ' => ' . $targetFile);
            }
        }
    }

    /**
     * Execute in single file mode (all types concatenated into one file).
     */
    protected function executeInSingleFileMode(
        OutputInterface $output,
        string $singleFileOutput,
        SourceFileCollection $sourceFileCollection
    ): void {
        $allContent = [];
        $targetFile = rtrim($this->outputDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $singleFileOutput;

        $output->writeln('Generating single file: ' . $targetFile);

        // Process all files and collect content
        foreach ($sourceFileCollection as $sourceFile) {
            $content = $this->parserService->getContent($sourceFile->sourceFile, true);

            if ($content) {
                $allContent[] = $content;
                $output->writeln('- ' . $sourceFile->sourceFile);
            }
        }

        // Process additional files (they don't require annotation)
        foreach ($this->additionalFiles as $additionalFile => $configArray) {
            $content = $this->parserService->getContent($additionalFile, false);

            if ($content) {
                $allContent[] = $content;
                $output->writeln('- ' . $additionalFile);
            }
        }

        if (!empty($allContent)) {
            $this->fs->dumpFile($targetFile, implode(PHP_EOL . PHP_EOL, $allContent) . PHP_EOL);
        }
    }

    public function processDirectory(string $inputDir, string $outputDir, bool $requireAnnotation, OutputInterface $io): void
    {
        // Normalize input directory to real path for comparison with Finder results
        $inputDir = realpath($inputDir);
        if ($inputDir === false) {
            return;
        }

        if ($inputDir[-1] !== DIRECTORY_SEPARATOR) {
            $inputDir .= DIRECTORY_SEPARATOR;
        }

        if ($outputDir[-1] !== DIRECTORY_SEPARATOR) {
            $outputDir .= DIRECTORY_SEPARATOR;
        }

        $files = $this->getPhpFiles($inputDir);

        $io->writeln('Processing directory: ' . $inputDir);

        foreach ($files as $sourceFileName) {
            $diffStart = strpos($sourceFileName, $inputDir) + strlen($inputDir);
            $diffEnd = strrpos($sourceFileName, DIRECTORY_SEPARATOR);
            $directoryDiff = substr($sourceFileName, $diffStart, $diffEnd - $diffStart + 1);
            $targetDirectory = rtrim($outputDir . $directoryDiff, DIRECTORY_SEPARATOR);

            // Set current target directory for import resolution
            $this->parserService->setCurrentTargetDirectory($targetDirectory);

            $content = $this->parserService->getContent($sourceFileName, $requireAnnotation);

            if ($content) {
                $targetFile = $outputDir . $directoryDiff . $this->parserService->getOutputFileName($sourceFileName);
                $this->fs->dumpFile($targetFile, $content);

                $io->writeln('- ' . $sourceFileName . ' => ' . $targetFile);
            }
        }
    }

    /**
     * Recursively find all PHP files in a directory.
     */
    private function getPhpFiles(string $directory): array
    {
        $finder = new Finder();
        $finder->files()
            ->in($directory)
            ->name('*.php')
            ->sortByName();

        $files = [];
        foreach ($finder as $file) {
            $files[] = $file->getRealPath();
        }

        return $files;
    }
}
