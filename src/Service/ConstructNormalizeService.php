<?php

namespace LeoVie\PhpConstructNormalize\Service;

use LeoVie\PhpConstructNormalize\Exception\ArrayKeyDoesNotExist;
use LeoVie\PhpConstructNormalize\Exception\CommandFailed;
use LeoVie\PhpConstructNormalize\Helper\ArrayHelper;
use LeoVie\PhpConstructNormalize\Helper\DiffHelper;
use LeoVie\PhpConstructNormalize\Helper\DirectoryHelper;
use Safe\Exceptions\JsonException;
use SebastianBergmann\Diff\Diff;
use SebastianBergmann\Diff\Parser;

class ConstructNormalizeService
{
    private const RECTOR_COMMAND = __DIR__ . '/../../vendor/bin/rector process %s --dry-run --clear-cache --output-format json';

    public function __construct(
        private ArrayHelper     $arrayHelper,
        private DirectoryHelper $directoryHelper,
        private DiffHelper      $diffHelper,
        private Parser          $parser,
    )
    {
    }

    public function normalizeMethod(string $methodCode): string
    {
        $method = '<?php ' . $methodCode;

        $tmpPathForMethod = $this->directoryHelper->createUniqueDirectory(__DIR__ . '/../../generated/');

        \Safe\file_put_contents($tmpPathForMethod . 'method.php', $method . "\n");

        $rectorResult = $this->runRector($tmpPathForMethod);
        $this->directoryHelper->deleteDirectory(['method.php'], $tmpPathForMethod);

        try {
            /** @var array<mixed> $json */
            $json = \Safe\json_decode($rectorResult, true);
            /** @var string $diff */
            $diff = $this->arrayHelper->extractFromArray(['file_diffs', 0, 'diff'], $json);
            /** @var Diff $parsedDiff */
            $parsedDiff = $this->arrayHelper->extractFromArray([0], $this->parser->parse($diff));
        } catch (JsonException|ArrayKeyDoesNotExist) {
            return $methodCode;
        }

        $newMethod = $this->diffHelper->reconstructNewFromOriginalAndDiff($method, $parsedDiff);

        /** @var string $newMethodCode */
        $newMethodCode = \Safe\preg_replace('@^<\?php @', '', $newMethod);

        return $newMethodCode;
    }

    private function runRector(string $tmpPathForMethod): string
    {
        $command = \Safe\sprintf(self::RECTOR_COMMAND, $tmpPathForMethod);
        $result = shell_exec($command);

        if (!is_string($result)) {
            return '';
        }

        return $result;
    }
}