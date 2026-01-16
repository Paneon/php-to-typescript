<?php

namespace Paneon\PhpToTypeScript\Tests\Services;

use Paneon\PhpToTypeScript\Tests\AbstractTestCase;

class ParserServiceConstructorPromotionArrayPrefixTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function doesNotPrefixBuiltInArrayGenericType()
    {
        $fixture = __DIR__ . '/../Fixtures/ConstructorArrayPromotionClass.php';

        $this->parserService->setPrefix('I');

        $content = $this->parserService->getInterfaceContent($fixture);

        $this->assertNotNull($content);
        $this->assertStringContainsString('interface IConstructorArrayPromotionClass', $content);

        // Main regression: should never become IArray<any>
        $this->assertStringContainsString('jobs: Array<any>;', $content);
        $this->assertStringNotContainsString('jobs: IArray<any>;', $content);
    }
}
