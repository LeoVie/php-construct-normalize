<?php

function foo(): array
{
    return array_map(function(int $x) {
        return $x * 2;
    }, [1, 2, 3]);
}

var_dump(foo());
