<?php

namespace LeoVie\PhpConstructNormalize\Helper;

interface NameGenerator
{
    public function generate(string $prefix = ''): string;
}