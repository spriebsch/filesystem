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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Filesystem::class)]
#[CoversClass(ExistingFile::class)]
#[CoversClass(FilesystemException::class)]
class FileSeparateTest extends TestCase
{
    public function test_exception_when_not_readable(): void
    {
        $this->expectException(FilesystemException::class);
        $this->expectExceptionMessage('Unable to load file');

        FileGetContents::failOnNextCall();

        $unused = ExistingFile::from(__DIR__ . '/../tests/testdata/file.txt')->load();
    }
}

class FileGetContents
{
    private static bool $failOnNextCall = false;

    public static function failOnNextCall(): void
    {
        self::$failOnNextCall = true;
    }

    public static function shouldFail(): bool
    {
        if (self::$failOnNextCall) {
            self::$failOnNextCall = false;

            return true;
        }

        return false;
    }
}

function file_get_contents(string $filename): string|false
{
    if (FileGetContents::shouldFail()) {
        return false;
    }

    return \file_get_contents($filename);
}
