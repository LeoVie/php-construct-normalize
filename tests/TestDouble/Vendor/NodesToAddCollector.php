<?php

namespace LeoVie\PhpConstructNormalize\Tests\TestDouble\Vendor;

use PhpParser\Node;
use Rector\PostRector\Contract\Collector\NodeCollectorInterface;

class NodesToAddCollector implements NodeCollectorInterface
{
    public array $addedNodesBeforeNode = [];

    public function isActive(): bool
    {
        return true;
    }

    /** @param Node[] $newNodes */
    public function addNodesBeforeNode(array $newNodes, \PhpParser\Node $positionNode): void
    {
//        var_dump('adding ' . count($newNodes) . ' new nodes');

        array_push($this->addedNodesBeforeNode, ...$newNodes);
    }
}