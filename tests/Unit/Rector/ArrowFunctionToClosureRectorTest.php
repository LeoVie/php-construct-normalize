<?php

namespace LeoVie\PhpConstructNormalize\Tests\Unit\Rector;

use LeoVie\PhpConstructNormalize\Rector\ArrowFunctionToClosureRector;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\BinaryOp\Mul;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\Return_;
use PHPUnit\Framework\TestCase;

class ArrowFunctionToClosureRectorTest extends TestCase
{
    /** @dataProvider refactorProvider */
    public function testRefactor(ArrowFunction $arrowFunction, Closure $expected): void
    {
        self::assertEquals($expected, (new ArrowFunctionToClosureRector())->refactor($arrowFunction));
    }

    public function refactorProvider(): array
    {
        $static = false;
        $byRef = false;
        $returnType = new Identifier('int');
        $params = [
            new Param(
                new Variable('x'),
                null,
                new Identifier('int')
            ),
        ];
        $expression = new Mul(
            new Variable('x'),
            new LNumber(2)
        );
        return [
            'fn -> function (without uses)' => [
                'arrowFunction' => new ArrowFunction(
                    [
                        'static' => $static,
                        'byRef' => $byRef,
                        'params' => $params,
                        'returnType' => $returnType,
                        'expr' => $expression,
                    ]
                ),
                'expected' => new Closure(
                    [
                        'static' => $static,
                        'byRef' => $byRef,
                        'params' => $params,
                        'returnType' => $returnType,
                        'stmts' => [
                            new Return_(
                                $expression
                            ),
                        ],
                    ]
                ),
            ],
        ];
    }
}