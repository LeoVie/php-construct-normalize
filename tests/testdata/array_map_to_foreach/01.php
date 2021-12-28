<?php

$array = ['a', 'ab', 'abc'];

$x = array_map(fn(string $chars): int => strlen($chars), $array);

var_dump($x);
