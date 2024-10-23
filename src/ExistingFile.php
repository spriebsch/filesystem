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

final class ExistingFile extends Filesystem implements File
{
    private string $filename;

    protected function __construct(string $filename)
    {
        $this->ensureFileExists($filename);

        $this->filename = realpath($filename);
    }

    public function isFile(): bool
    {
        $this->ensureFileStillExists();

        return true;
    }

    public function isDirectory(): bool
    {
        $this->ensureFileStillExists();

        return false;
    }

    public function directory(): Directory
    {
        $this->ensureFileStillExists();

        return ExistingDirectory::from(dirname($this->asString()));
    }

    public function require(): mixed
    {
        $this->ensureFileStillExists();

        return require $this->asString();
    }

    public function load(): string
    {
        $this->ensureFileStillExists();

        $content = file_get_contents($this->filename);

        if ($content === false) {
            throw FilesystemException::unableToLoad($this->filename);
        }

        return $content;
    }

    public function overwrite(string $content): string
    {
        $this->ensureFileStillExists();

        file_put_contents($this->filename, $content);
    }

    public function asString(): string
    {
        return $this->filename;
    }

    private function ensureFileExists(string $filename): void
    {
        $realpath = realpath($filename);

        if ($realpath === false) {
            throw FilesystemException::fileDoesNotExist($filename);
        }

        if (!is_file($realpath)) {
            throw FilesystemException::noFile($filename);
        }
    }

    private function ensureFileStillExists(): void
    {
        if (!is_file($this->filename)) {
            throw FilesystemException::fileDoesNotExistAnyMore($this->filename);
        }
    }
}
