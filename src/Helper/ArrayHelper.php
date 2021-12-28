<?php

namespace LeoVie\PhpConstructNormalize\Helper;

use LeoVie\PhpConstructNormalize\Exception\ArrayKeyDoesNotExist;

class ArrayHelper
{
    /**
     * @param array<int|string> $keys
     * @param mixed[] $array
     */
    public function extractFromArray(array $keys, array $array): mixed
    {
        if (empty($keys)) {
            return null;
        }

        $key = array_shift($keys);
        if (!array_key_exists($key, $array)) {
            throw new ArrayKeyDoesNotExist();
        }

        if (empty($keys)) {
            return $array[$key];
        }

        return $this->extractFromArray($keys, $array[$key]);
    }
}