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

use DirectoryIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class ExistingDirectory extends Filesystem implements Directory
{
    private string $directory;

    public static function create(string $directory): self
    {
        if (is_dir($directory)) {
            throw FilesystemException::directoryExists($directory);
        }

        if (!@mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw FilesystemException::failedToCreateDirectory($directory);
        }

        return new self($directory);
    }

    protected function __construct(string $directory)
    {
        $this->ensureDirectoryExists($directory);

        $this->directory = realpath($directory);
    }

    public function file(string $filename): File
    {
        $this->ensureFilenameDoesNotContainSlashes($filename);

        return ExistingFile::from($this->directory . '/' . $filename);
    }

    public function isFile(): bool
    {
        $this->ensureDirectoryStillExists();

        return false;
    }

    public function isDirectory(): bool
    {
        $this->ensureDirectoryStillExists();

        return true;
    }

    public function createDirectory(string $subdirectory): Directory
    {
        $this->ensureDirectoryStillExists();

        return ExistingDirectory::create($this->asString() . '/' . $subdirectory);
    }

    public function createFile(string $filename, string $content): File
    {
        $this->ensureDirectoryStillExists();
        $this->ensureFilenameDoesNotContainSlashes($filename);

        $path = $this->directory . '/' . $filename;

        if (is_file($path)) {
            throw FilesystemException::fileExists($path);
        }

        $result = file_put_contents($path, $content);

        if ($result === false || !is_file($path)) {
            throw FilesystemException::failedToCreateFile($path);
        }

        return Filesystem::from($path);
    }

    public function deleteFile(string $filename): void
    {
        $this->ensureDirectoryStillExists();
        $this->ensureFilenameDoesNotContainSlashes($filename);

        if (!is_file($this->directory . '/' . $filename)) {
            return;
        }

        unlink($this->directory . '/' . $filename);
    }

    public function deleteDirectory(string $subdirectory): void
    {
        $this->ensureDirectoryStillExists();
        $this->ensureFilenameDoesNotContainSlashes($subdirectory);

        rmdir($this->directory . '/' . $subdirectory);
    }

    public function subdirectory(string $subdirectory): Directory
    {
        $this->ensureDirectoryStillExists();
        $this->ensureIsSubdirectory($this->directory, $subdirectory);

        return new self($this->directory . '/' . $subdirectory);
    }

    public function parentDirectory(): Directory
    {
        $this->ensureDirectoryStillExists();

        return new self(dirname($this->directory));
    }

    public function isEmpty(): bool
    {
        $this->ensureDirectoryStillExists();

        return count($this->allFiles()) === 0;
    }

    public function allFiles(): array
    {
        $this->ensureDirectoryStillExists();

        $result = [];

        foreach (new DirectoryIterator($this->directory) as $item) {
            if ($item->isDot()) {
                continue;
            }

            $result[] = Filesystem::from($item->getPathname());
        }

        return $result;
    }

    public function allFilesRecursively(): array
    {
        $this->ensureDirectoryStillExists();

        $result = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->directory, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $item) {
            $result[] = Filesystem::from($item->getPathname());
        }

        return $result;
    }

    public function deleteAllFiles(): void
    {
        $this->ensureDirectoryStillExists();

        foreach ($this->allFiles() as $file) {
            $this->deleteFile(basename($file->asString()));
        }
    }

    public function deleteAllFilesAndDirectoriesRecursively(): void
    {
        $this->ensureDirectoryStillExists();

        $directories = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->directory)
        );

        foreach ($iterator as $file) {
            if ($file->getBasename() === '..') {
                continue;
            }

            $path = $file->getPathname();

            if (is_file($path)) {
                ExistingDirectory::from(dirname($path))->deleteFile(basename($path));
            } else {
                $directories[] = realpath($path);
            }
        }

        $directories = array_reverse(array_unique($directories));

        foreach ($directories as $directory) {
            ExistingDirectory::from($directory)->parentDirectory()->deleteDirectory(basename($directory));
        }
    }

    public function asString(): string
    {
        $this->ensureDirectoryStillExists();

        return $this->directory;
    }

    private function ensureDirectoryExists(string $directory): void
    {
        $realpath = realpath($directory);

        if ($realpath === false) {
            throw FilesystemException::directoryDoesNotExist($directory);
        }

        if (!is_dir($realpath)) {
            throw FilesystemException::noDirectory($directory);
        }
    }

    private function ensureFilenameDoesNotContainSlashes(string $filename): void
    {
        if (str_contains($filename, '/')) {
            throw FilesystemException::invalidFilename($filename);
        }
    }

    private function ensureIsSubdirectory(string $directory, string $subdirectory): void
    {
        if (str_starts_with($subdirectory, '/')) {
            throw FilesystemException::invalidSubdirectory($subdirectory);
        }
    }

    private function ensureDirectoryStillExists(): void
    {
        if (!is_dir($this->directory)) {
            throw FilesystemException::directoryDoesNotExistAnyMore($this->directory);
        }
    }
}
