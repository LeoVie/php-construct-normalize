<?php

namespace LeoVie\PhpConstructNormalize\Helper;

interface NameGenerator
{
    public static function create(): self;

    public function generate(string $prefix = ''): string;
}