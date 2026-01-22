PHP To TypeScript Parser
======

[![Build](https://github.com/Paneon/php-to-typescript/actions/workflows/main.yml/badge.svg)](https://github.com/Paneon/php-to-typescript/actions/workflows/main.yml)
[![Latest Stable Version](https://poser.pugx.org/paneon/php-to-typescript/v/stable)](https://packagist.org/packages/paneon/php-to-typescript)
[![Total Downloads](https://poser.pugx.org/paneon/php-to-typescript/downloads)](https://packagist.org/packages/paneon/php-to-typescript)
[![License](https://poser.pugx.org/paneon/php-to-typescript/license)](https://packagist.org/packages/paneon/php-to-typescript)

A library which can be used to create TypeScript classes/interfaces based on PHP classes and enums. Main use case is in a scenario where a PHP backend is consumed by a JavaScript/TypeScript frontend and serialized DTOs are consumed.

TypeScript is a superscript of JavaScript that adds strong typing and other features on top of JS.
Automatically generated classes can be useful, for example when using a simple JSON API to communicate to a JavaScript client.
This way you can get typing for your API responses in an easy way.

Feel free to build on this or use as inspiration to build something completely different.

## Installation

```bash
$ composer require paneon/php-to-typescript
```

## Features

- Generate TypeScript interfaces from PHP classes
- Generate TypeScript enums from PHP enums (string-backed, int-backed, or unit enums)
- Optional string literal union type output for enums
- Support for nullable types
- Configurable prefix/suffix for generated type names
- Export keyword support

## Classes

Mark your PHP classes with the `#[TypeScript]` attribute to generate TypeScript interfaces.

#### Example source file:
```php
<?php

use Paneon\PhpToTypeScript\Attribute\TypeScript;

#[TypeScript]
class Example
{
    public string $firstName;
    public ?string $middleName;
    public string $lastName;
    public ?int $age;

    /** @var Contact[] */
    public array $contacts;
}
```

#### Default output file:

```typescript
interface Example {
  firstName: string;
  middleName: string;
  lastName: string;
  age: number;
  contacts: Contact[];
}
```

## Enums

PHP 8.1+ enums are also supported. Mark your enums with the `#[TypeScript]` attribute.

#### String-backed enum:
```php
<?php

use Paneon\PhpToTypeScript\Attribute\TypeScript;

#[TypeScript]
enum Status: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Completed = 'completed';
}
```

#### Output:
```typescript
enum Status {
  Pending = 'pending',
  Active = 'active',
  Completed = 'completed',
}
```

#### Int-backed enum:
```php
<?php

use Paneon\PhpToTypeScript\Attribute\TypeScript;

#[TypeScript]
enum Priority: int
{
    case Low = 1;
    case Medium = 2;
    case High = 3;
}
```

#### Output:
```typescript
enum Priority {
  Low = 1,
  Medium = 2,
  High = 3,
}
```

#### Unit enum (no backing value):
```php
<?php

use Paneon\PhpToTypeScript\Attribute\TypeScript;

#[TypeScript]
enum Color
{
    case Red;
    case Green;
    case Blue;
}
```

#### Output:
```typescript
enum Color {
  Red,
  Green,
  Blue,
}
```

### String Literal Union Types

Use `asUnion: true` to output enums as string literal union types instead of TypeScript enums:

```php
<?php

use Paneon\PhpToTypeScript\Attribute\TypeScript;

#[TypeScript(asUnion: true)]
enum Status: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Completed = 'completed';
}
```

#### Output:
```typescript
type Status = 'pending' | 'active' | 'completed';
```

You can also set this globally:
```php
$parserService->setUseEnumUnionType(true);
```

## Null-aware Types
Since [TypeScript 2.0](https://www.typescriptlang.org/docs/handbook/release-notes/typescript-2-0.html#null--and-undefined-aware-types)
Null and optional/undefined types are supported. In the generator bundle, this is an optional feature and null types will be removed by default. To include nullable types use
```bash
$ php bin/console typescript:generate --nullable
```


#### Output file with null types:

```typescript
interface Example {
  firstName: string;
  middleName: string|null;
  lastName: string;
  age: number|null;
  contacts: Contact[];
}
```

## Output Format Options

By default, the parser generates TypeScript interface declarations (`.d.ts` files). You can customize the output format using these options:

### Type Syntax

Use `setUseType(true)` to generate `type` aliases instead of `interface` declarations. This also changes the file extension from `.d.ts` to `.ts`.

```php
$parserService->setUseType(true);
```

#### Output with type syntax:

```typescript
type Example = {
  firstName: string;
  middleName: string;
  lastName: string;
  age: number;
  contacts: Contact[];
};
```

### Export Keyword

Use `setExport(true)` to prefix the output with the `export` keyword.

```php
$parserService->setExport(true);
```

#### Output with export:

```typescript
export interface Example {
  firstName: string;
  middleName: string;
  lastName: string;
  age: number;
  contacts: Contact[];
}
```

### Combining Options

You can combine both options to generate exported type aliases:

```php
$parserService
    ->setUseType(true)
    ->setExport(true);
```

#### Output with both options:

```typescript
export type Example = {
  firstName: string;
  middleName: string;
  lastName: string;
  age: number;
  contacts: Contact[];
};
```

## API Usage

Use the `ParserService` to convert PHP files to TypeScript:

```php
use Paneon\PhpToTypeScript\Services\ParserService;
use Paneon\PhpToTypeScript\Parser\PhpDocParser;

$parserService = new ParserService($logger, new PhpDocParser());

// Auto-detect class or enum and generate appropriate TypeScript
$content = $parserService->getContent('src/DTO/Example.php');

// Or use specific methods
$interfaceContent = $parserService->getInterfaceContent('src/DTO/Example.php');
$enumContent = $parserService->getEnumContent('src/Enum/Status.php');
```

## Import Generation

When using the `export` keyword, referenced types need to be imported. The parser can automatically generate import statements when you provide a `SourceFileCollection`:

```php
use Paneon\PhpToTypeScript\Model\SourceFile;
use Paneon\PhpToTypeScript\Model\SourceFileCollection;

// Register all files to be converted
$sourceFiles = new SourceFileCollection();
$sourceFiles->add(new SourceFile(
    'App\DTO\CartResponseDTO',
    'src/DTO/CartResponseDTO.php',
    'assets/types'
));
$sourceFiles->add(new SourceFile(
    'App\DTO\ProductDetailDTO',
    'src/DTO/ProductDetailDTO.php',
    'assets/types'
));

$parserService
    ->setExport(true)
    ->setSourceFiles($sourceFiles)
    ->setCurrentTargetDirectory('assets/types');

$content = $parserService->getContent('src/DTO/CartResponseDTO.php');
```

#### Output with imports:
```typescript
import { ProductDetailDTO } from './ProductDetailDTO';

export interface CartResponseDTO {
  count: number;
  total: string;
  items: ProductDetailDTO[];
}
```

### Single File Mode

If you're generating all types into a single file, use `setSingleFileMode(true)` to disable import generation:

```php
$parserService
    ->setExport(true)
    ->setSingleFileMode(true);
```

## Usage of the Command 'typescript:generate-single'

The purpose of the generate command is to create TypeScript definitions for classes from external packages where you
can't add the `#[TypeScript]` attribute (e.g. vendor code), but their classes are used in your code.
It will only affect a single file and needs a specific target location if you don't want it directly inside assets/js/interfaces.

```bash
$ php bin/console typescript:generate-single src/DTO/ProductTeaser.php assets/types/
```

It's recommended to trigger the generation of theses interfaces after `composer update/install`.
