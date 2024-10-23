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

interface Directory
{
    public function file(string $filename): File;

    public function exists(string $filename): bool;

    public function isFile(): bool;

    public function isDirectory(): bool;

    public function createDirectory(string $subdirectory): Directory;

    public function createFile(string $filename, string $content): File;

    public function deleteFile(string $filename): void;

    public function deleteDirectory(string $subdirectory): void;

    public function subdirectory(string $subdirectory): Directory;

    public function parentDirectory(): Directory;

    public function isEmpty(): bool;

    public function allFiles(): array;

    public function allFilesRecursively(): array;

    public function deleteAllFiles(): void;

    public function deleteAllFilesAndDirectoriesRecursively(): void;

    public function asString(): string;
}