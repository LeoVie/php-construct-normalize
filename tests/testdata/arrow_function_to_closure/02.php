<?php

function getFunction(): callable
{
    return fn(int $x, int $y): string => "$x -> $y";
}