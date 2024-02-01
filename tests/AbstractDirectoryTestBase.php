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

use PHPUnit\Framework\TestCase;

abstract class AbstractDirectoryTestBase extends TestCase
{
    private function cleanUpTestDirectory(Directory $testDirectory): void
    {
        if ($testDirectory instanceof FakeDirectory) {
            return;
        }

        $testDirectory->deleteAllFilesAndDirectoriesRecursively();
    }

    public function test_a_directory_is_no_file(): void
    {
        $file = $this->directory();

        $this->assertTrue($file->isDirectory());
        $this->assertFalse($file->isFile());
    }

    public function test_file_can_be_created(): void
    {
        $directory = $this->directory();
        $filename = uniqid('the-filename-', true);
        $content = 'the-content';

        $directory->createFile($filename, $content);

        if ($directory instanceof ExistingDirectory) {
            $path = $directory->asString() . '/' . $filename;

            $this->assertStringEqualsFile($path, $content);

            unlink($path);
        } else {
            $files = $directory->allFiles();

            $this->assertCount(1, $files);

            $file = $files[0];

            $this->assertSame($filename, $file->asString());
            $this->assertSame($content, $file->load());
        }
    }

    public function test_existing_file_cannot_be_created(): void
    {
        $directory = $this->directory();
        $filename = uniqid('the-filename-', true);
        $file = $directory->createFile($filename, 'the-content');

        $this->expectException(FilesystemException::class);
        $this->expectExceptionMessage(sprintf('File "%s" exists', $file->asString()));

        $directory->createFile($filename, 'the-content');
    }

    abstract protected function directory(): Directory;
}