<?php

$characters = array_merge(range('A', 'Z'), range('a', 'z'));

$name = join('',
    array_map(fn(): string => $characters[rand(0, count($characters) - 1)], range(0, 30))
);

var_dump($name);