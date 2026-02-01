<?php

namespace Paneon\PhpToTypeScript\Tests\Services;

use Paneon\PhpToTypeScript\Tests\AbstractTestCase;

class ParserServicePrefixLiteralTypesTest extends AbstractTestCase
{
    public function testDoesNotPrefixLiteralTypes(): void
    {
        $fixture = __DIR__ . '/../Fixtures/LiteralUnionTypeClass.php';
        $this->parserService->setPrefix('I');
        $content = $this->parserService->getInterfaceContent($fixture);

        // Should NOT prefix 'false'
        $this->assertStringContainsString('maybeObject: IAnotherObject|false;', $content);
        $this->assertStringNotContainsString('Ifalse', $content);

        // Should NOT prefix 'true'
        $this->assertStringContainsString('maybeTrue: string|true;', $content);
        $this->assertStringNotContainsString('Itrue', $content);

        // Should NOT prefix 'null' (with includeTypeNullable enabled)
        $this->parserService->setIncludeTypeNullable(true);
        $content = $this->parserService->getInterfaceContent($fixture);
        $this->assertStringContainsString('maybeNull: ISomeClass|null;', $content);
        $this->assertStringNotContainsString('Inull', $content);
    }
}
