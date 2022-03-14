<?php

declare(strict_types=1);

namespace LeoVie\PhpConstructNormalize\Tests\Unit\Helper;

use LeoVie\PhpConstructNormalize\Helper\DirectoryHelper;
use LeoVie\PhpConstructNormalize\Helper\NameGenerator;
use PHPUnit\Framework\TestCase;

class DirectoryHelperTest extends TestCase
{
    private const TESTDATA_DIR = __DIR__ . '/../../testdata/directory_helper/';
    private const EXISTING_DIRECTORY_NAME = 'already_existing_directory';
    private const NOT_EXISTING_DIRECTORY_NAME = 'not_existing_directory';
    private const NOT_EXISTING_DIRECTORY_PATH = self::TESTDATA_DIR . self::NOT_EXISTING_DIRECTORY_NAME . '/';

    protected function setUp(): void
    {
        $this->deleteNotExistingDirectory();
        self::assertDirectoryDoesNotExist(self::NOT_EXISTING_DIRECTORY_PATH);
    }

    protected function tearDown(): void
    {
        $this->deleteNotExistingDirectory();
    }

    private function deleteNotExistingDirectory(): void
    {
        if (realpath(self::NOT_EXISTING_DIRECTORY_PATH) !== false) {
            shell_exec('rm -f ' . self::NOT_EXISTING_DIRECTORY_PATH . '*');
            rmdir(self::NOT_EXISTING_DIRECTORY_PATH);
        }
    }

    public function testCreateUniqueDirectory(): void
    {
        $nameGenerator = $this->createMock(NameGenerator::class);
        $nameGenerator->method('generate')->willReturn(
            self::NOT_EXISTING_DIRECTORY_NAME
        );

        $newlyCreatedDir = (new DirectoryHelper($nameGenerator))
            ->createUniqueDirectory(self::TESTDATA_DIR);

        self::assertSame(self::NOT_EXISTING_DIRECTORY_PATH, $newlyCreatedDir);
        self::assertDirectoryExists(self::NOT_EXISTING_DIRECTORY_PATH);
    }

    public function testCreateUniqueDirectorySkipsExistingDirectory(): void
    {
        $nameGenerator = $this->createMock(NameGenerator::class);
        $nameGenerator->method('generate')->willReturnOnConsecutiveCalls(
            self::EXISTING_DIRECTORY_NAME,
            self::NOT_EXISTING_DIRECTORY_NAME
        );

        $newlyCreatedDir = (new DirectoryHelper($nameGenerator))
            ->createUniqueDirectory(self::TESTDATA_DIR);

        self::assertSame(self::NOT_EXISTING_DIRECTORY_PATH, $newlyCreatedDir);
        self::assertDirectoryExists(self::NOT_EXISTING_DIRECTORY_PATH);
    }

    /** @dataProvider deleteDirectoryProvider */
    public function testDeleteDirectory(array $filesInsideDirectory): void
    {
        mkdir(self::NOT_EXISTING_DIRECTORY_PATH);
        self::assertDirectoryExists(self::NOT_EXISTING_DIRECTORY_PATH);
        foreach ($filesInsideDirectory as $file) {
            touch(self::NOT_EXISTING_DIRECTORY_PATH . $file);
            self::assertFileExists(self::NOT_EXISTING_DIRECTORY_PATH . $file);
        }

        (new DirectoryHelper($this->createMock(NameGenerator::class)))
            ->deleteDirectory($filesInsideDirectory, self::NOT_EXISTING_DIRECTORY_PATH);

        self::assertDirectoryDoesNotExist(self::NOT_EXISTING_DIRECTORY_PATH);
        foreach ($filesInsideDirectory as $file) {
            self::assertFileDoesNotExist(self::NOT_EXISTING_DIRECTORY_PATH . $file);
        }
    }

    public function deleteDirectoryProvider(): array
    {
        return [
            'no files' => [
                'filesInsideDirectory' => [],
            ],
            'with files' => [
                'filesInsideDirectory' => ['a.txt', 'b.json', 'c.php'],
            ],
        ];
    }
}