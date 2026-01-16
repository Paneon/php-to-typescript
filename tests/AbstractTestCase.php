<?php

namespace Paneon\PhpToTypeScript\Tests;

use Exception;
use Monolog\Handler\PHPConsoleHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Paneon\PhpToTypeScript\Parser\PhpDocParser;
use Paneon\PhpToTypeScript\Services\ParserService;
use PHPUnit\Framework\TestCase;

abstract class AbstractTestCase extends TestCase
{
    protected ParserService $parserService;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->parserService = new ParserService(
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

        if(in_array('-vvv', $_SERVER['argv'], true)){
            $logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));
        }
        else if(in_array('-vv', $_SERVER['argv'], true)){
            $logger->pushHandler(new StreamHandler('php://stdout', Logger::INFO));
        }
        else if(in_array('-v', $_SERVER['argv'], true)){
            $logger->pushHandler(new StreamHandler('php://stdout', Logger::WARNING));
        }

        $logger->pushHandler(new StreamHandler(__DIR__ . '/../var/dev/test.log', Logger::DEBUG));

        return $logger;
    }

}
