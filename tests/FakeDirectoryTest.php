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
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(FakeDirectory::class)]
#[CoversClass(FilesystemException::class)]
#[UsesClass(FakeFile::class)]
class FakeDirectoryTest extends AbstractDirectoryTestBase
{
    private const PATH = '/some/path/that/does/not/even/exist';

    protected function directory(): Directory
    {
        return new FakeDirectory(self::PATH);
    }

    public function test_can_check_file_existence(): void
    {
        $directory = new FakeDirectory(self::PATH);
        $directory->createFile('the-filename', 'the-content');

        $this->assertTrue(
            $directory->exists('the-filename')
        );

        $this->assertFalse(
            $directory->exists('does-not-exist')
        );
    }

    public function test_can_be_converted_to_string(): void
    {
        $directory = new FakeDirectory(self::PATH);

        $this->assertSame(self::PATH, $directory->asString());
    }

    public function test_subdirectory_can_be_created(): void
    {
        $subdirectory = 'the-subdirectory';
        $directory = (new FakeDirectory(self::PATH))->createDirectory($subdirectory);

        $this->assertSame(self::PATH . '/' . $subdirectory, $directory->asString());
    }

    public function test_directory_containing_a_file_is_not_empty(): void
    {
        $directory = $this->directory();
        $directory->createFile('the-filename', 'the-content');

        $this->assertFalse($directory->isEmpty());
    }
}
