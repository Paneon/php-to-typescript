<?php

namespace Paneon\PhpToTypeScript\Tests\Fixtures;

use Paneon\PhpToTypeScript\Attribute\TypeScript;

#[TypeScript(asUnion: true)]
enum SuitUnionEnum: string
{
    case Hearts = 'hearts';
    case Diamonds = 'diamonds';
    case Clubs = 'clubs';
    case Spades = 'spades';
}
