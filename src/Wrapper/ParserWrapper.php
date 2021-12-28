<?php

namespace LeoVie\PhpConstructNormalize\Wrapper;

use SebastianBergmann\Diff\Parser;

final class ParserWrapper
{
    public function create(): Parser
    {
        return new Parser();
    }
}