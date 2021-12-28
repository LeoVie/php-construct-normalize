<?php

namespace LeoVie\PhpConstructNormalize\Helper;

class DirectoryHelper
{
    public function createUniqueDirectory(string $path): string
    {
        $createdDirectoryPath = $path . (new RandomNameGenerator())->generate() . '/';

        if (realpath($createdDirectoryPath) !== false) {
            return $this->createUniqueDirectory($path);
        }

        mkdir($createdDirectoryPath);

        return $createdDirectoryPath;
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