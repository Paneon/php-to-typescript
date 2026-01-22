<?php

namespace Paneon\PhpToTypeScript\Tests\Fixtures;

use Paneon\PhpToTypeScript\Attribute\TypeScript;

#[TypeScript]
enum SuitEnum: string
{
    case Hearts = 'hearts';
    case Diamonds = 'diamonds';
    case Clubs = 'clubs';
    case Spades = 'spades';
}
