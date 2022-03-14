<?php

namespace LeoVie\PhpConstructNormalize\Helper;

class DirectoryHelper
{
    public function __construct(
        private NameGenerator $nameGenerator
    )
    {}

    public function createUniqueDirectory(string $path): string
    {
        $createdDirectoryPath = $path . $this->nameGenerator->generate() . '/';

        if ($this->directoryExists($createdDirectoryPath)) {
            return $this->createUniqueDirectory($path);
        }

        mkdir($createdDirectoryPath);

        return $createdDirectoryPath;
    }

    private function directoryExists(string $path): bool
    {
        return realpath($path) !== false;
    }

    /** @param string[] $filesInsideDirectory */
    public function deleteDirectory(array $filesInsideDirectory, string $path): void
    {
        foreach ($filesInsideDirectory as $file) {
            unlink($path . $file);
        }

        rmdir($path);
    }
}