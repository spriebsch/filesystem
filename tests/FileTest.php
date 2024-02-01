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

#[CoversClass(ExistingFile::class)]
#[CoversClass(Filesystem::class)]
#[CoversClass(FilesystemException::class)]
class FileTest extends TestCase
{
    public function test_when_does_not_exist(): void
    {
        $this->expectException(FilesystemException::class);
        $this->expectExceptionMessage('does not exist');

        $unused = Filesystem::from(__DIR__ . '/../tests/testdata/does-not-exist');
    }

    public function test_is_a_file(): void
    {
        $file = Filesystem::from(__DIR__ . '/../tests/testdata/file.txt');

        $this->assertTrue($file->isFile());
    }

    public function test_is_no_directory(): void
    {
        $file = Filesystem::from(__DIR__ . '/../tests/testdata/file.txt');

        $this->assertFalse($file->isDirectory());
    }

    public function test_can_be_loaded(): void
    {
        $file = Filesystem::from(__DIR__ . '/../tests/testdata/file.txt');

        $this->assertStringEqualsFile($file->asString(), $file->load());
    }
}
