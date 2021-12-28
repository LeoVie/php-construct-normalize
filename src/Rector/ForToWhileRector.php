<?php

namespace LeoVie\PhpConstructNormalize\Rector;

use PhpParser\Node;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

class ForToWhileRector extends AbstractRector
{
    public function getNodeTypes(): array
    {
        return [Node\Stmt\For_::class];
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change for to while. Currently, only simple foreach calls like for ($i = 0; $i < 10; i++) are supported.', [
                new CodeSample(
                    'for ($i = 0; $i < 10; $i++) { $x = $array[$i]; print($x); }',
                    '$i = 0; while ($i < 10) { $x = array[$i]; print($x); $i++; }'
                ),
            ]
        );
    }

    public function refactor(Node $node)
    {
        /** @var Node\Stmt\For_ $for */
        $for = $node;

        $init = $for->init;
        $cond = $for->cond;
        $loop = $for->loop;
        $statements = $for->stmts;

        $statements[] = new Expression($loop[0]);
        return [
            new Expression($init[0]),
            new Node\Stmt\While_(
                $cond[0],
                $statements
            ),
        ];
    }
}