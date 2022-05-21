<?php

namespace LeoVie\PhpConstructNormalize\Service;

use LeoVie\PhpConstructNormalize\Helper\DirectoryHelper;

class ConstructNormalizeService
{
    private const VENDOR_PATH = __DIR__ . '/../../../..';
    private const RECTOR_PATH = self::VENDOR_PATH . '/bin/rector';
    private const RECTOR_CONFIGS_PATH = __DIR__ . '/../..';
    private const RECTOR_COMMANDS = [
        self::RECTOR_PATH . ' process %s --clear-cache --output-format json --config=' . self::RECTOR_CONFIGS_PATH . '/rector_01.php',
        self::RECTOR_PATH . ' process %s --clear-cache --output-format json --config=' . self::RECTOR_CONFIGS_PATH . '/rector_02.php',
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
            $command = sprintf($rectorCommand, $tmpPathForMethod);
            shell_exec($command);
        }
    }
}