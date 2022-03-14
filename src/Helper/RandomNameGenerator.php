<?php

namespace LeoVie\PhpConstructNormalize\Helper;

class RandomNameGenerator implements NameGenerator
{
    private const NAME_LENGTH = 30;

    /** @var string[] */
    private array $alphabet;

    public static function create(): self
    {
        return new self();
    }

    // TODO: make private
    public function __construct()
    {
        $this->alphabet = $this->createAlphabet();
    }

    /** @return array<int, string> */
    private function createAlphabet(): array
    {
        return array_merge(range('A', 'Z'), range('a', 'z'));
    }

    public function generate(string $prefix = ''): string
    {
        return $prefix . $this->generateRandomString(self::NAME_LENGTH);
    }

    private function generateRandomString(int $length): string
    {
        return join('',
            array_map(
                fn(int $_): string => $this->pickRandomCharacter(),
                range(1, $length)
            )
        );
    }

    private function pickRandomCharacter(): string
    {
        return $this->alphabet[rand(0, count($this->alphabet) - 1)];
    }
}