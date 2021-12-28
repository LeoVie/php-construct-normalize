<?php

namespace LeoVie\PhpConstructNormalize\Helper;

class RandomNameGenerator implements NameGenerator
{
    public function generate(string $prefix = ''): string
    {
        $characters = array_merge(range('A', 'Z'), range('a', 'z'));

        return $prefix . join('',
                array_map(fn(int $_): string => $characters[rand(0, count($characters) - 1)], range(0, 30))
            );
    }
}