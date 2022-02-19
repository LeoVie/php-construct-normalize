<?php

declare(strict_types=1);

namespace LeoVie\PhpConstructNormalize\Tests\Unit\Helper;

use LeoVie\PhpConstructNormalize\Helper\RandomNameGenerator;
use PHPUnit\Framework\TestCase;

class RandomNameGeneratorTest extends TestCase
{
    /** @dataProvider generateProvider */
    public function testGenerate(string $prefix): void
    {
        $randomName = (RandomNameGenerator::create())->generate($prefix);

        self::assertTrue(str_starts_with($randomName, $prefix));
        self::assertSame(
            strlen($prefix) + 30,
            strlen($randomName)
        );
        self::assertMatchesRegularExpression('@([A-Z][a-z])|([a-z][A-Z])@', $randomName);
    }

    public function generateProvider(): array
    {
        return [
            'no prefix' => [
                'prefix' => '',
            ],
            'with prefix' => [
                'prefix' => 'foo'
            ],
        ];
    }
}