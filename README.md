PHP To TypeScript Parser
======

[![Build](https://github.com/Paneon/php-to-typescript/actions/workflows/main.yml/badge.svg)](https://github.com/Paneon/php-to-typescript/actions/workflows/main.yml)

A library which can be used to create TypeScript classes/interfaces based on PHP classes. Main use case is in a scenario where a PHP backend is consumed by a JavaScript/TypeScript frontend and serialized DTOs are consumed.

TypeScript is a superscript of JavaScript that adds strong typing and other features on top of JS. 
Automatically generated classes can be useful, for example when using a simple JSON API to communicate to a JavaScript client. 
This way you can get typing for your API responses in an easy way.

Feel free to build on this or use as inspiration to build something completely different.

## Installation

```bash
$ composer require paneon/php-to-typescript
```

## Features

WIP

#### Example source file:
```php
<?php

/**
 * @TypeScriptInterface
 */
class Example
{
    /**
     * @var string
     */
    public $firstName;

    /**
     * @var string|null
     */
    public $middleName;

    /**
     * @var string
     */
    public $lastName;

    /**
     * @var int|null
     */
    public $age;
    
    /** @var Contact[] */
    public $contacts;
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
$ php bin/console typescript:generate --nullable
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


## Usage of the Command 'typescript:generate-single'

The purpose of the generate Command is to create TypeScript definitions for Classes from external packages where you 
can't add the TypeScriptInterface-Annotation but their classes are for example used in your classes. 
It will only affect a single file and needs a specific target location if you don't want it directly inside assets/js/interfaces.

```bash
$ php bin/console typescript:generate-single vendor/shopping/s2-communication-bundle/src/CommunicationBundle/DTO/ProductTeaser.php assets/js/interfaces/s2-communication-bundle/DTO/
```

It's recommended to trigger the generation of theses interfaces after `composer update/install`.
