<?php

namespace LeoVie\PhpConstructNormalize\Rector;

use LeoVie\PhpConstructNormalize\Helper\NameGenerator;
use LeoVie\PhpConstructNormalize\Helper\RandomNameGenerator;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Continue_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

class ArrayMapToForeachRector extends AbstractRector
{
    private string $transformedArrayVarName = '';
    /** @var class-string<NameGenerator> */
    protected string $nameGeneratorClass = RandomNameGenerator::class;

    public function getNodeTypes(): array
    {
        return [Node\Expr\Assign::class, Return_::class];
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change array_map calls to foreach. Currently, only simple array_map calls like array_map(fn, array) are supported.', [
                new CodeSample(
                    '$transformed = array_map(function (int $x): int { return $x * 2; }, [1, 2, 3]);',
                    '$transformed = []; foreach ([1, 2, 3] as $x) { $transformed[] = $x * 2; }'
                ),
            ]
        );
    }

    public function refactor(Node $node): Node|array|null
    {
        if ($node instanceof Assign || $node instanceof Return_) {
            return $this->refactorTyped($node);
        }

        return $node;
    }

    private function refactorTyped(Assign|Return_ $node): Assign|Return_
    {
        if (!$node->expr instanceof FuncCall) {
            return $node;
        }

        /** @var FuncCall $funcCall */
        $funcCall = $node->expr;

        if (!$funcCall->name instanceof Node\Name) {
            return $node;
        }

        if ($funcCall->name->getFirst() !== 'array_map') {
            return $node;
        }


        if (count($funcCall->getRawArgs()) > 2) {
            // currently, only simple array_map calls like array_map(fn, array) are supported.
            return $node;
        }

        /** @var NameGenerator $nameGenerator */
        $nameGenerator = $this->nameGeneratorClass::create();
        $this->transformedArrayVarName = $nameGenerator->generate();

        $refactoredArrayMapFuncCall = $this->refactorArrayMapFuncCall($funcCall);

        $this->nodesToAddCollector->addNodesBeforeNode($refactoredArrayMapFuncCall, $node);

        $node->expr = new Variable($this->transformedArrayVarName);

        return $node;
    }

    /** @return Stmt[] */
    private function refactorArrayMapFuncCall(FuncCall $arrayMapFuncCall): array
    {
        /** @var Node\Arg $firstArg */
        $firstArg = $arrayMapFuncCall->getRawArgs()[0];
        /** @var Closure $closure */
        $closure = $firstArg->value;

        /** @var Node\Arg $secondArg */
        $secondArg = $arrayMapFuncCall->getRawArgs()[1];
        $array = $secondArg->value;

        $transformedArrayCreation = new Assign(
            new Variable($this->transformedArrayVarName),
            new Array_()
        );

        $foreach = new Foreach_(
            $array,
            $closure->getParams()[0]->var,
            [
                'stmts' => $this->replaceReturnsWithAssignPlusContinues($closure->getStmts()),
            ]
        );

        return [
            new Node\Stmt\Expression($transformedArrayCreation),
            $foreach,
        ];
    }

    /**
     * @param Stmt[] $statements
     *
     * @return array<Stmt|Continue_|Expression>
     */
    private function replaceReturnsWithAssignPlusContinues(array $statements): array
    {
        $replaced = [];
        foreach ($statements as $statement) {
            array_push($replaced, ...$this->replaceIfReturn($statement));
        }

        return $replaced;
    }

    /** @return array<Stmt|Continue_|Expression> */
    private function replaceIfReturn(Stmt $statement): array
    {
        if (!$statement instanceof Return_) {
            return [$statement];
        }

        return $this->replaceReturnWithAssignPlusContinue($statement);
    }

    /** @return array<Continue_|Expression> */
    private function replaceReturnWithAssignPlusContinue(Return_ $return): array
    {
        if ($return->expr === null) {
            return [new Continue_()];
        }

        return [
            new Expression(
                new Assign(
                    new ArrayDimFetch(
                        new Variable($this->transformedArrayVarName)
                    ),
                    $return->expr
                )
            ),
            new Continue_(),
        ];
    }
}