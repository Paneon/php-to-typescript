PHP Classes to TypeScript Bundle
======

[![Build](https://github.com/Paneon/php-to-typescript-bundle/actions/workflows/main.yml/badge.svg?branch=main)](https://github.com/Paneon/php-to-typescript-bundle/actions/workflows/main.yml)
[![Latest Stable Version](https://poser.pugx.org/paneon/php-to-typescript-bundle/v/stable)](https://packagist.org/packages/paneon/php-to-typescript-bundle)
[![Total Downloads](https://poser.pugx.org/paneon/php-to-typescript-bundle/downloads)](https://packagist.org/packages/paneon/php-to-typescript-bundle)
[![License](https://poser.pugx.org/paneon/php-to-typescript-bundle/license)](https://packagist.org/packages/paneon/php-to-typescript-bundle)

A Symfony bundle that adds a command to extract [TypeScript interface](https://www.typescriptlang.org/docs/handbook/interfaces.html)
from PHP classes. Based on [the example from Martin Vseticka](https://stackoverflow.com/questions/33176888/export-php-interface-to-typescript-interface-or-vice-versa?answertab=votes#tab-top)
this bundle uses [the PHP-Parser library](https://github.com/nikic/PHP-Parser) and docblock annotations.

TypeScript is a superscript of JavaScript that adds strong typing and other features on top of JS.
Automatically generated classes can be useful, for example when using a simple JSON API to communicate to a JavaScript client.
This way you can get typing for your API responses in an easy way.

Feel free to build on this or use as inspiration to build something completely different.

## Installation

As a Symfony bundle you'll need to start by adding the package to your project with Composer:

```bash
composer require paneon/php-to-typescript-bundle
```

## Usage of the Command 'typescript:generate'

The purpose of the generate Command is to create TypeScript definitions for all Classes in your source root which are
under your immediate control (i.e. You can change their source).
It will only affect classes which have the *@TypeScriptInterface*-Annotation.

```bash
php bin/console typescript:generate
```

The command scans directories recursively for all `.php` files.
It will only generate Type Definitions (interfaces) for files with the appropriate annotation.
The default parameters will scan for alle PHP Classes inside "src/" and output them as TypeScript Interfaces into
"assets/js/interfaces/" while keeping the relative directory structure.

#### Examples:

| Source File          | Output File                            |
|----------------------|----------------------------------------|
| src/Model/Person.php | assets/js/interfaces/Model/Person.d.ts |
| src/Example.php      | assets/js/interfaces/Example.d.ts      |

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

## Null-aware Types
Since [TypeScript 2.0](https://www.typescriptlang.org/docs/handbook/release-notes/typescript-2-0.html#null--and-undefined-aware-types)
Null and optional/undefined types are supported. In the generator bundle, this is an optional feature and null types will be removed by default. To include nullable types use
```bash
php bin/console typescript:generate --nullable
```


#### Output file with null types:

```typescript
interface Example {
    firstName: string;
    middleName: string|null;
    lastName: string;
    age: number;
    contacts: Contact[];
}
```

## Type Aliases instead of Interfaces

Use the `--use-type` option to generate TypeScript `type` aliases instead of `interface` declarations. This will also change the output file extension from `.d.ts` to `.ts`:

```bash
php bin/console typescript:generate --use-type
```

#### Output with --use-type:

```typescript
type Example = {
    firstName: string;
    middleName: string;
    lastName: string;
    age: number;
    contacts: Contact[];
};
```

## Export Declarations

Use the `--export` option to add the `export` keyword before declarations:

```bash
php bin/console typescript:generate --export
```

#### Output with --export:

```typescript
export interface Example {
    firstName: string;
    middleName: string;
    lastName: string;
    age: number;
    contacts: Contact[];
}
```

You can combine options:

```bash
php bin/console typescript:generate --export --use-type
```

#### Output with --export --use-type:

```typescript
export type Example = {
    firstName: string;
    middleName: string;
    lastName: string;
    age: number;
    contacts: Contact[];
};
```

## Import Resolution

When using the `--export` option, the generator automatically adds `import` statements for referenced types. This enables proper TypeScript module resolution between generated files.

For example, if you have a `User` class that references an `Address` class:

```php
<?php

use Paneon\PhpToTypeScript\Attribute\TypeScript;

#[TypeScript]
class User
{
    public string $name;
    public Address $address;
}
```

#### Output with --export (multi-file mode):

```typescript
// User.d.ts
import { Address } from './Address';

export interface User {
    name: string;
    address: Address;
}
```

The import paths are automatically calculated based on the relative directory structure of the generated files.

## Single File Mode

Instead of generating individual files for each class/enum, you can output all TypeScript types into a single file using the `--single-file` option:

```bash
# Use default filename (types.ts)
php bin/console typescript:generate --export --single-file

# Specify custom filename
php bin/console typescript:generate --export --single-file=all-types.ts
```

In single file mode:
- All types are concatenated into a single output file
- No `import` statements are generated (all types are in the same file)
- Directory structure is not preserved

#### Output with --export --single-file:

```typescript
// types.ts
export interface Address {
    street: string;
    city: string;
}

export interface User {
    name: string;
    address: Address;
}

export enum Status {
    Active = 'active',
    Inactive = 'inactive',
}
```

## Enum Support

PHP 8.1+ enums with the `#[TypeScript]` attribute are automatically processed alongside classes:

```php
<?php

use Paneon\PhpToTypeScript\Attribute\TypeScript;

#[TypeScript]
enum Status: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Pending = 'pending';
}
```

#### Default enum output:

```typescript
enum Status {
    Active = 'active',
    Inactive = 'inactive',
    Pending = 'pending',
}
```

#### With --enum-union-type option:

Use `--enum-union-type` to output enums as string literal union types:

```bash
php bin/console typescript:generate --enum-union-type
```

```typescript
type Status = 'active' | 'inactive' | 'pending';
```

## Configuration Options

You can configure these options in `config/packages/php_to_typescript.yaml`:

```yaml
php_to_typescript:
    indentation: 2
    inputDirectory: src/
    outputDirectory: assets/js/interfaces/
    prefix: ''
    suffix: ''
    nullable: false
    useType: false           # Generate type aliases instead of interfaces
    export: false            # Add export keyword to declarations
    useEnumUnionType: false  # Output enums as union types
    singleFileMode: false    # Output all types to a single file
    singleFileOutput: 'types.ts'  # Filename for single file mode
```


## Usage of the Command 'typescript:generate-single'

The purpose of the generate Command is to create TypeScript definitions for Classes from external packages where you
can't add the TypeScriptInterface-Annotation but their classes are for example used in your classes.
It will only affect a single file and needs a specific target location if you don't want it directly inside assets/js/interfaces.

```bash
php bin/console typescript:generate-single vendor/some-package/src/SomeDir/DTO/SomeoneElsesClass.php assets/js/external/some-package/
```

### Options for generate-single

- `--nullable`: Include null types in output
- `--use-type`: Generate type alias instead of interface
- `--export`: Add export keyword before declaration
- `--enum-union-type`: Output enums as union types

The command automatically detects whether the file contains a class or enum and processes it accordingly.

It's recommended to trigger the generation of interfaces after `composer update/install`.
