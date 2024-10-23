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

interface File
{
    public function isFile(): bool;

    public function isDirectory(): bool;

    public function directory(): Directory;

    public function require(): mixed;

    public function load(): string;

    public function overwrite(string $content): void;

    public function asString(): string;
}