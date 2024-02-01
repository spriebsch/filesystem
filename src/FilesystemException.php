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

use RuntimeException;

class FilesystemException extends RuntimeException
{
    public static function doesNotExist(string $name): self
    {
        return new self(sprintf('File or directory "%s" does not exist', $name));
    }

    public static function directoryDoesNotExist(string $directory): self
    {
        return new self(sprintf('Directory "%s" does not exist', $directory));
    }

    public static function fileDoesNotExist(string $filename): self
    {
        return new self(sprintf('File "%s" does not exist', $filename));
    }

    public static function fileDoesNotExistAnyMore(string $filename): self
    {
        return new self(sprintf('File "%s" does not exist any more', $filename));
    }

    public static function directoryDoesNotExistAnyMore(string $directory): self
    {
        return new self(sprintf('Directory "%s" does not exist any more', $directory));
    }

    public static function fileExists(string $filename): self
    {
        return new self(sprintf('File "%s" exists', $filename));
    }

    public static function directoryExists(string $directory): self
    {
        return new self(sprintf('Directory "%s" exists', $directory));
    }

    public static function failedToCreateDirectory(string $directory): self
    {
        return new self(sprintf('Failed to create directory "%s"', $directory));
    }

    public static function failedToCreateFile(string $filename): self
    {
        return new self(sprintf('Failed to create file "%s"', $filename));
    }

    public static function unableToLoad(string $filename): self
    {
        return new self(sprintf('Unable to load file "%s"', $filename));
    }

    public static function invalidFilename(string $filename): self
    {
        return new self(sprintf('Invalid filename "%s"', $filename));
    }

    public static function invalidSubdirectory(string $subdirectory): self
    {
        return new self(sprintf('Invalid subdirectory "%s"', $subdirectory));
    }

    public static function noDirectory(string $directory): self
    {
        return new self(sprintf('"%s" is not a directory', $directory));
    }

    public static function noFile(string $filename): self
    {
        return new self(sprintf('"%s" is not a file', $filename));
    }
}
