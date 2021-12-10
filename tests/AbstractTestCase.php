<?php

namespace Paneon\PhpToTypeScript\Tests;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\DocParser;
use Exception;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Paneon\PhpToTypeScript\Parser\PhpDocParser;
use Paneon\PhpToTypeScript\Services\ParserService;
use PHPUnit\Framework\TestCase;

abstract class AbstractTestCase extends TestCase
{
    protected ParserService $parserService;

    /**
     * @throws AnnotationException
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->parserService = new ParserService(
            new AnnotationReader(new DocParser()),
            $this->createLogger(),
            new PhpDocParser()
        );
    }

    /**
     * @throws Exception
     */
    protected function createLogger(): Logger
    {
        $logger = new Logger('test');
        $logger->pushHandler(new StreamHandler(__DIR__ . '/../var/dev/test.log'));

        return $logger;
    }

}
