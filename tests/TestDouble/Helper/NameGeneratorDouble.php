<?php

namespace LeoVie\PhpConstructNormalize\Tests\TestDouble\Helper;

use LeoVie\PhpConstructNormalize\Helper\NameGenerator;

class NameGeneratorDouble implements NameGenerator
{
    public static function create(): self
    {
        return new self();
    }

    public function generate(string $prefix = ''): string
    {
        return $prefix . '_GENERATED';
    }
}