<?php

namespace LeoVie\PhpConstructNormalize\Tests\Unit\Rector;

use LeoVie\PhpConstructNormalize\Tests\TestDouble\Helper\NameGeneratorDouble;
use LeoVie\PhpConstructNormalize\Tests\TestDouble\Rector\ArrayMapToForeachRectorDouble;
use LeoVie\PhpConstructNormalize\Tests\TestDouble\Vendor\NodesToAddCollector;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Mul;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\Continue_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\Return_;
use PHPUnit\Framework\TestCase;

class ArrayMapToForeachRectorTest extends TestCase
{
    /** @dataProvider refactorProvider */
    public function testRefactor(Assign|Return_ $assignOrReturn, Assign|Return_ $expected, array $expectedNodesBefore): void
    {
        $nodesToAddCollector = new NodesToAddCollector();
        $arrayMapToForeachRector = new ArrayMapToForeachRectorDouble();
        $arrayMapToForeachRector->setNodesToAddCollector($nodesToAddCollector);
        $arrayMapToForeachRector->setNameGeneratorClass(NameGeneratorDouble::class);

        self::assertEquals($expected, $arrayMapToForeachRector->refactor($assignOrReturn));
        self::assertEquals($expectedNodesBefore, $nodesToAddCollector->addedNodesBeforeNode);
    }

    public function refactorProvider(): array
    {
        $array = new Array_([
            new ArrayItem(
                new LNumber(1)
            ),
            new ArrayItem(
                new LNumber(2)
            ),
            new ArrayItem(
                new LNumber(3)
            ),
        ]);

        $statement = new Mul(
            new Variable('x'),
            new LNumber(2)
        );

        return [
            [
                'assignOrReturn' => new Assign(
                    new Variable('foo'),
                    new FuncCall(
                        new Name('array_map'), [
                        new Arg(
                            new Closure([
                                'static' => false,
                                'byRef' => false,
                                'params' => [
                                    new Param(
                                        new Variable('x'),
                                        null,
                                        new Identifier('int')
                                    ),
                                ],
                                'returnType' => new Identifier('int'),
                                'stmts' => [
                                    new Return_(
                                        $statement
                                    ),
                                ],
                            ])
                        ),
                        new Arg(
                            $array
                        ),
                    ])
                ),
                'expected' => new Assign(
                    new Variable('foo'),
                    new Variable('_GENERATED')
                ),
                'expectedNodesBefore' => [
                    new Expression(
                        new Assign(
                            new Variable('_GENERATED'),
                            new Array_()
                        )
                    ),
                    new Foreach_(
                        $array,
                        new Variable('x'),
                        [
                            'stmts' => [
                                new Expression(
                                    new Assign(
                                        new ArrayDimFetch(
                                            new Variable('_GENERATED')
                                        ),
                                        $statement
                                    )
                                ),
                                new Continue_(),
                            ],
                        ]
                    ),
                ],
            ],
            [
                'assignOrReturn' => new Return_(
                    new FuncCall(
                        new Name('array_map'), [
                        new Arg(
                            new Closure([
                                'static' => false,
                                'byRef' => false,
                                'params' => [
                                    new Param(
                                        new Variable('x'),
                                        null,
                                        new Identifier('int')
                                    ),
                                ],
                                'returnType' => new Identifier('int'),
                                'stmts' => [
                                    new Return_(
                                        $statement
                                    ),
                                ],
                            ])
                        ),
                        new Arg(
                            $array
                        ),
                    ])
                ),
                'expected' => new Return_(
                    new Variable('_GENERATED')
                ),
                'expectedNodesBefore' => [
                    new Expression(
                        new Assign(
                            new Variable('_GENERATED'),
                            new Array_()
                        )
                    ),
                    new Foreach_(
                        $array,
                        new Variable('x'),
                        [
                            'stmts' => [
                                new Expression(
                                    new Assign(
                                        new ArrayDimFetch(
                                            new Variable('_GENERATED')
                                        ),
                                        $statement
                                    )
                                ),
                                new Continue_(),
                            ],
                        ]
                    ),
                ],
            ],
        ];
    }
}