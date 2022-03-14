<?php

namespace LeoVie\PhpConstructNormalize\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ArrowFunction;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

class ArrowFunctionToClosureRector extends AbstractRector
{
    public function getNodeTypes(): array
    {
        return [ArrowFunction::class];
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change arrow functions to closures.', [
                new CodeSample(
                    'fn(int $x): int => $x * 2;',
                    'function (int $x) : int { return $x * 2; };'
                ),
            ]
        );
    }

    public function refactor(Node $node): Node|array|null
    {
        /** @var ArrowFunction $arrowFunction */
        $arrowFunction = $node;

        return new Closure(
            [
                'static' => $arrowFunction->static,
                'byRef' => $arrowFunction->byRef,
                'params' => $arrowFunction->params,
                // 'uses' => $arrowFunction->uses TODO: Existiert in Arrow-Funktionen nicht: Prüfen, ob Variablen in Assign- oder Param-Nodes vorkommen -> wenn nein: zu uses hinzufügen
                'returnType' => $arrowFunction->returnType,
                'stmts' => $arrowFunction->getStmts(),
                'attrGroups' => $arrowFunction->attrGroups,
            ],
            $arrowFunction->getAttributes()
        );
    }
}