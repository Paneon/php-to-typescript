# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

PHP To TypeScript Parser - A library that generates TypeScript interfaces from PHP classes marked with attributes. Useful when a PHP backend serves JSON to a JavaScript/TypeScript frontend.

## Development Commands

```bash
composer install           # Install dependencies
composer run build         # Run lint + tests (use before committing)
composer run lint          # PHPStan analyze src --level=5
composer run test          # PHPUnit tests

# Run single test
vendor/bin/phpunit --filter testMethodName
```

## Architecture

**Entry Point:** `ParserService::getInterfaceContent(string $sourceFile)` - analyzes a PHP file and returns TypeScript interface string.

**Conversion Flow:**
1. PHP file is parsed via nikic/php-parser to extract namespace/class name
2. ReflectionClass loads the class and checks for `#[TypeScriptInterface]` attribute
3. Properties are analyzed via reflection + PHPDoc parsing
4. `PhpDocParser::getTypeEquivalent()` maps PHP types to TypeScript
5. `DeclareInterface` builds the TypeScript output string

**Key Files:**
- `src/Services/ParserService.php` - Main orchestrator, configurable via setPrefix/setSuffix/setIndent/setIncludeTypeNullable
- `src/Parser/PhpDocParser.php` - Parses `@var`/`@return` comments, maps PHP→TS types
- `src/Parser/DeclareInterface.php` - Builds TypeScript interface output
- `src/Annotation/` - PHP 8 attributes: TypeScriptInterface, Type, Exclude, VirtualProperty

**Type Detection Priority:**
1. `#[Type('CustomType')]` attribute (highest)
2. Native PHP type hints
3. PHPDoc `@var` or `@return`
4. Falls back to `any`

**Type Mappings:** `int/float` → `number`, `bool` → `boolean`, `DateTime` → `string`, `array` → `Array<any>`, `mixed` → `any`

## Testing

Tests are in `tests/Services/`. Test fixtures (example PHP classes) are in `tests/Fixtures/`.
Debug logs write to `var/dev/test.log`. Verbosity: `-v` (warning), `-vv` (info), `-vvv` (debug).
