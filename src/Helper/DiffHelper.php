<?php

namespace LeoVie\PhpConstructNormalize\Helper;

use SebastianBergmann\Diff\Diff;
use SebastianBergmann\Diff\Line;

class DiffHelper
{
    public function reconstructNewFromOriginalAndDiff(string $original, Diff $diff): string
    {
        $originalLines = $this->parseLines($original);

        $newLines = [];
        $lastChunkEnd = 0;

        foreach ($diff->getChunks() as $chunk) {
            if ($chunk->getStart() !== $lastChunkEnd) {
                array_push($newLines, ...$this->extractOriginalLines($originalLines, $lastChunkEnd, $chunk->getStart() - 2));
            }

            $linesWithoutRemovedLines = array_filter($chunk->getLines(), fn(Line $line): bool => $line->getType() !== Line::REMOVED);

            $lastChunkEnd = $chunk->getStart() + count($linesWithoutRemovedLines);

            array_push($newLines, ...array_map(fn(Line $line): string => $line->getContent(), $linesWithoutRemovedLines));
        }

        array_push($newLines, ...$this->extractOriginalLines($originalLines, $lastChunkEnd - 1, count($originalLines) - 1));

        return join("\n", $newLines);
    }

    /**
     * @param string[] $originalLines
     *
     * @return string[]
     */
    private function extractOriginalLines(array $originalLines, int $lineStart, int $lineEnd): array
    {
        $extracted = [];
        for ($i = $lineStart; $i <= $lineEnd; $i++) {
            $extracted[] = $originalLines[$i];
        }

        return $extracted;
    }

    /** @return string[] */
    private function parseLines(string $text): array
    {
        $text = str_replace("\r", '', $text);

        return explode("\n", $text);
    }
}