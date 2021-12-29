<?php

namespace LeoVie\PhpConstructNormalize\Rector;

use PhpParser\Node;
use PhpParser\Node\Stmt;
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

        $loopVarChange = new Expression($loop[0]);
        $statements[] = $loopVarChange;

        return [
            new Expression($init[0]),
            new Node\Stmt\While_(
                $cond[0],
                $this->prependContinuesWithLoopVarChange($statements, $loopVarChange)
            ),
        ];
    }

    private function prependContinuesWithLoopVarChange(array $statements, Expression $loopVarChange): array
    {
        $replaced = [];
        foreach ($statements as $statement) {
            array_push($replaced, ...$this->prependIfContinue($statement, $loopVarChange));
        }

        return $replaced;
    }

    private function prependIfContinue(Stmt $statement, Expression $loopVarChange): array
    {
        if (!$statement instanceof Stmt\Continue_) {
            return [$statement];
        }

        return [
            $loopVarChange,
            $statement
        ];
    }
}