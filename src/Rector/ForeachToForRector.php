<?php

namespace LeoVie\PhpConstructNormalize\Rector;

use LeoVie\PhpConstructNormalize\Helper\RandomNameGenerator;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Smaller;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\PostInc;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Foreach_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

class ForeachToForRector extends AbstractRector
{
    public function getNodeTypes(): array
    {
        return [Foreach_::class];
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change foreach to for. Currently, only simple foreach calls like foreach($array as $item) are supported.', [
                new CodeSample(
                    'foreach ([1, 2, 3] as $x) { print($x); }',
                    'for ($i = 0; $i < count([1, 2, 3]); $i++) { $x = [1, 2, 3][$i]; print($x); }'
                ),
            ]
        );
    }

    public function refactor(Node $node): Node|array|null
    {
        /** @var Foreach_ $foreach */
        $foreach = $node;

        $randomNameGenerator = new RandomNameGenerator();
        $arrayCacheVarName = $randomNameGenerator->generate('arrayCache_');
        $arrayKeysCacheVarName = $randomNameGenerator->generate('arrayKeysCache_');
        $loopVarName = $randomNameGenerator->generate('loopVar_');

        $arrayCache = $this->cacheForeachExprIntoArrayCacheVar($arrayCacheVarName, $foreach->expr);
        $arrayKeysCache = $this->cacheArrayKeysIntoArrayKeysCacheVar($arrayKeysCacheVarName, $arrayCacheVarName);

        $init = [
            new Assign(
                new Variable($loopVarName),
                new LNumber(0)
            ),
        ];
        $cond = [
            new Smaller(
                new Variable($loopVarName),
                $this->countFuncCall($arrayCacheVarName)
            ),
        ];

        $loop = [
            new PostInc(
                new Variable($loopVarName)
            ),
        ];

        $forStatements = $this->prependValueVarFetch(
            $foreach->stmts,
            $foreach->valueVar,
            $arrayCacheVarName,
            $loopVarName
        );

        if ($foreach->keyVar !== null) {
            $forStatements = $this->prependKeyVarFetch(
                $forStatements,
                $foreach->keyVar,
                $arrayKeysCacheVarName,
                $loopVarName
            );
        }

        return [
            $arrayCache,
            $arrayKeysCache,
            new For_(
                [
                    'init' => $init,
                    'cond' => $cond,
                    'loop' => $loop,
                    'stmts' => $forStatements,
                ]
            ),
        ];
    }

    private function cacheForeachExprIntoArrayCacheVar(string $arrayCacheVariableName, Expr $foreachExpr): Expression
    {
        return new Expression(
            new Assign(
                new Variable($arrayCacheVariableName),
                $foreachExpr
            )
        );
    }

    private function cacheArrayKeysIntoArrayKeysCacheVar(string $arrayKeysCacheVarName, string $arrayCacheVarName): Expression
    {
        return new Expression(
            new Assign(
                new Variable($arrayKeysCacheVarName),
                new FuncCall(
                    new Name('array_keys'),
                    [
                        new Arg(
                            new Variable($arrayCacheVarName)
                        ),
                    ]
                )
            )
        );
    }

    private function countFuncCall(string $arrayVarName): FuncCall
    {
        return new FuncCall(
            new Name('count'),
            [
                new Arg(
                    new Variable($arrayVarName)
                ),
            ]
        );
    }

    /**
     * @param Stmt[] $statements
     *
     * @return Stmt[]
     */
    private function prependValueVarFetch(array $statements, Expr $valueVar, string $arrayVarName, string $loopVarName): array
    {
        return $this->prependVarFetch($statements, $valueVar, $arrayVarName, $loopVarName);
    }

    /**
     * @param Stmt[] $statements
     *
     * @return Stmt[]
     */
    private function prependKeyVarFetch(array $statements, Expr $keyVar, string $arrayVarName, string $loopVarName): array
    {
        return $this->prependVarFetch($statements, $keyVar, $arrayVarName, $loopVarName);
    }

    /**
     * @param Stmt[] $statements
     *
     * @return Stmt[]
     */
    private function prependVarFetch(array $statements, Expr $var, string $arrayVarName, string $loopVarName): array
    {
        array_unshift($statements,
            new Expression(
                new Assign(
                    $var,
                    new ArrayDimFetch(
                        new Variable($arrayVarName),
                        new Variable($loopVarName)
                    )
                )
            )
        );

        return $statements;
    }
}