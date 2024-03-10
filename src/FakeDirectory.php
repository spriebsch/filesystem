<?php declare(strict_types=1);

/*
 * This file is part of Filesystem.
 *
 * (c) Stefan Priebsch <stefan@priebsch.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spriebsch\filesystem;

final class FakeDirectory implements Directory
{
    private string $path;
    private array $subdirectories = [];
    private array $files          = [];

    public function __construct(string $path)
    {
        if (str_ends_with($path, '/')) {
            $path = substr($path, 0, -1);
        }

        $this->path = $path;
    }

    public function file(string $filename): File
    {
        if (!isset($this->files[$filename])) {
            throw FilesystemException::doesNotExist($filename);
        }

        return new FakeFile($filename, $this->files[$filename]->load());
    }

    public function isFile(): bool
    {
        return false;
    }

    public function isDirectory(): bool
    {
        return true;
    }

    public function createDirectory(string $subdirectory): Directory
    {
        $this->subdirectories[] = $subdirectory;

        return new self($this->path . '/' . $subdirectory);
    }

    public function createFile(string $filename, string $content): File
    {
        if (isset($this->files[$filename])) {
            throw FilesystemException::fileExists($filename);
        }

        $file = new FakeFile($filename, $content);

        $this->files[$filename] = $file;

        return $file;
    }

    public function deleteFile(string $filename): void
    {
        unset($this->files[$filename]);
    }

    public function deleteDirectory(string $subdirectory): void
    {
        unset($this->subdirectories[$subdirectory]);
    }

    public function subdirectory(string $subdirectory): Directory
    {
        return new self($this->path . '/' . $subdirectory);
    }

    public function parentDirectory(): Directory
    {
        return new self(dirname($this->path));
    }

    public function isEmpty(): bool
    {
        return count($this->files) === 0 && count($this->subdirectories) === 0;
    }

    public function allFiles(): array
    {
        return array_values($this->files);
    }

    public function allFilesRecursively(): array
    {
        return array_merge($this->files, $this->subdirectories);
    }

    public function deleteAllFiles(): void
    {
        $this->files = [];
    }

    public function deleteAllFilesAndDirectoriesRecursively(): void
    {
        $this->files = [];
        $this->subdirectories = [];
    }

    public function asString(): string
    {
        return $this->path;
    }
}
