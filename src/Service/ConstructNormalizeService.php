<?php

namespace LeoVie\PhpConstructNormalize\Service;

use LeoVie\PhpConstructNormalize\Helper\DirectoryHelper;

class ConstructNormalizeService
{
    private const RECTOR_COMMANDS = [
        __DIR__ . '/../../vendor/bin/rector process %s --clear-cache --output-format json --config=' . __DIR__ . '/../../rector_01.php',
        __DIR__ . '/../../vendor/bin/rector process %s --clear-cache --output-format json --config=' . __DIR__ . '/../../rector_02.php',
    ];

    public function __construct(private DirectoryHelper $directoryHelper)
    {
    }

    public function normalizeMethod(string $methodCode): string
    {
        $method = '<?php ' . $methodCode;

        $tmpPathForMethod = $this->directoryHelper->createUniqueDirectory(__DIR__ . '/../../generated/');
        $tmpMethodFile = $tmpPathForMethod . 'method.php';

        \Safe\file_put_contents($tmpMethodFile, $method . "\n");

        $this->runRector($tmpPathForMethod);

        $newMethod = \Safe\file_get_contents($tmpMethodFile);

        $this->directoryHelper->deleteDirectory(['method.php'], $tmpPathForMethod);

        /** @var string $newMethodCode */
        $newMethodCode = \Safe\preg_replace('@^<\?php @', '', $newMethod);

        return $newMethodCode;
    }

    private function runRector(string $tmpPathForMethod): void
    {
        foreach (self::RECTOR_COMMANDS as $rectorCommand) {
            $command = \Safe\sprintf($rectorCommand, $tmpPathForMethod);
            shell_exec($command);
        }
    }
}