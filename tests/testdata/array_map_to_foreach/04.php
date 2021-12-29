<?php

function foo(array $items, int $n): array
{
    return array_map(fn(int $x): int => $x * $n, $items);
}
