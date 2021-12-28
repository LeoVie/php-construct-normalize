<?php

namespace LeoVie\PhpConstructNormalize\Tests\TestDouble\Rector;

use LeoVie\PhpConstructNormalize\Rector\ArrayMapToForeachRector;
use Rector\PostRector\Contract\Collector\NodeCollectorInterface;

class ArrayMapToForeachRectorDouble extends ArrayMapToForeachRector
{
    public function setNodesToAddCollector(NodeCollectorInterface $nodesToAddCollector): void
    {
        $this->nodesToAddCollector = $nodesToAddCollector;
    }

    public function setNameGeneratorClass(string $nameGeneratorClass): void
    {
        $this->nameGeneratorClass = $nameGeneratorClass;
    }
}