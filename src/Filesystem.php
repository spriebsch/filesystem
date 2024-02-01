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

abstract class Filesystem
{
    public static function from(string $name): File|Directory
    {
        if (is_file($name)) {
            return new ExistingFile($name);
        }

        if (is_dir($name)) {
            return new ExistingDirectory($name);
        }

        throw FilesystemException::doesNotExist($name);
    }

    abstract public function isFile(): bool;

    abstract public function isDirectory(): bool;

    abstract public function asString(): string;
}
