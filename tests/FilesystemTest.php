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
#[CoversClass(ExistingDirectory::class)]
#[CoversClass(ExistingFile::class)]
#[CoversClass(FilesystemException::class)]
class FilesystemTest extends TestCase
{
    public function test_can_be_created_from_file(): void
    {
        $this->assertInstanceOf(ExistingFile::class, Filesystem::from(__FILE__));
    }

    public function test_can_be_created_from_directory(): void
    {
        $this->assertInstanceOf(ExistingDirectory::class, Filesystem::from(__DIR__));
    }

    public function test_directory_can_have_trailing_slash(): void
    {
        $this->assertInstanceOf(ExistingDirectory::class, Filesystem::from(__DIR__ . '/'));
    }

    public function test_exception_when_file_or_directory_does_not_exist(): void
    {
        $this->expectException(FilesystemException::class);
        $this->expectExceptionMessage('File or directory "/does-not-exist" does not exist');

        Filesystem::from('/does-not-exist');
    }

    public function test_exception_when_directory_cannot_be_created(): void
    {
        $this->expectException(FilesystemException::class);
        $this->expectExceptionMessage('Failed to create');

        ExistingDirectory::create('/proc/should-not-be-allowed');
    }

    public function test_a_file_is_no_directory(): void
    {
        $file = Filesystem::from(__FILE__);

        $this->assertTrue($file->isFile());
        $this->assertFalse($file->isDirectory());
    }

    public function test_a_directory_is_no_file(): void
    {
        $file = Filesystem::from(__DIR__);

        $this->assertTrue($file->isDirectory());
        $this->assertFalse($file->isFile());
    }

    public function test_file_can_be_created(): void
    {
        $temporaryDirectory = $this->temporaryDirectory();

        $filename = uniqid('test-filesystem-', true);
        $content = 'the-content';

        $directory = ExistingDirectory::from($temporaryDirectory);
        $directory->createFile($filename, $content);

        $this->assertEquals($content, trim(file_get_contents($temporaryDirectory . '/' . $filename)));

        unlink($temporaryDirectory . '/' . $filename);
    }

    /*
    public function test_a_directory_can_be_retrieved_from_file(): void
    {
        $file = Filesystem::from(__FILE__);

        $this->assertEquals(__DIR__, $file->directory()->asString());
    }
    */

    public function test_directory_is_not_empty(): void
    {
        $this->assertFalse(Filesystem::from(__DIR__)->isEmpty());
    }

    public function test_directory_is_empty(): void
    {
        $directory = $this->testDirectory();

        $this->assertTrue($directory->isEmpty());

        $this->cleanUpTestDirectory($directory);
    }

    public function test_exception_when_file_to_create_exists(): void
    {
        $directory = $this->testDirectory();
        $filename = uniqid('test-filesystem-', true);
        $file = $directory->createFile($filename, 'the-content');

        $this->expectException(FilesystemException::class);
        $this->expectExceptionMessage(sprintf('File "%s" exists', $file->asString()));

        $directory->createFile($filename, 'the-content');
    }

    public function test_directory_contents_can_be_deleted(): void
    {
        $directory = $this->testDirectory();
        $directory->createFile('the-filename', 'the-content');

        $directory->deleteAllFiles();

        $this->assertTrue($directory->isEmpty());

        $this->cleanUpTestDirectory($directory);
    }

    public function test_create_directory(): void
    {
        $directory = $this->testDirectory();
        $name = $directory->asString() . '/' . uniqid('test-directory-', true);

        ExistingDirectory::create($name);

        $this->assertDirectoryExists($name);

        $this->cleanUpTestDirectory($directory);
    }

    public function test_subdirectory_can_be_created(): void
    {
        $directory = $this->testDirectory();
        $subdirectory = uniqid('test-directory-', true);

        $directory->createDirectory($subdirectory);

        $this->assertDirectoryExists($directory->asString() . '/' . $subdirectory);

        $this->cleanUpTestDirectory($directory);
    }

    public function test_directory_contents_can_be_deleted_recursively(): void
    {
        $directory = $this->testDirectory();
        $name = $directory->asString();
        $directory->createFile('the-filename', 'the-content');
        $subdirectory = $directory->createDirectory('the-subdirectory');
        $subdirectory->createFile('the-filename', 'the-content');

        $directory->deleteAllFilesAndDirectoriesRecursively();

        $this->assertDirectoryDoesNotExist($name);
    }

    public function test_can_access_subdirectory(): void
    {
        $parentDirectory = Filesystem::from(__DIR__ . '/..');
        $subdirectory = array_slice(explode('/', __DIR__), -1)[0];

        $this->assertEquals(__DIR__, $parentDirectory->subdirectory($subdirectory)->asString());
    }

    public function test_can_access_file(): void
    {
        $this->assertEquals(__FILE__, Filesystem::from(__DIR__)->file(basename(__FILE__))->asString());
    }

    public function test_iterates_over_directory(): void
    {
        $files = Filesystem::from(__DIR__ . '/testdata/directory')->allFiles();

        $files = array_map(
            fn(Filesystem $file) => $file->asString(),
            $files
        );

        $this->assertCount(3, $files);

        $this->assertContains(__DIR__ . '/testdata/directory/1', $files);
        $this->assertContains(__DIR__ . '/testdata/directory/2', $files);
        $this->assertContains(__DIR__ . '/testdata/directory/subdirectory', $files);
    }

    public function test_iterates_over_directory_recursively(): void
    {
        $files = Filesystem::from(__DIR__ . '/testdata/directory')->allFilesRecursively();

        $files = array_map(
            fn(Filesystem $file) => $file->asString(),
            $files
        );

        $this->assertCount(3, $files);

        $this->assertContains(__DIR__ . '/testdata/directory/1', $files);
        $this->assertContains(__DIR__ . '/testdata/directory/2', $files);
        $this->assertContains(__DIR__ . '/testdata/directory/subdirectory/3', $files);
    }

    public function test_exception_on_load_when_file_was_externally_deleted(): void
    {
        $this->whenFileWasDeletedExpectExceptionOnMethodCall('load');
    }

    public function test_exception_on_directory_when_file_was_externally_deleted(): void
    {
        $this->whenFileWasDeletedExpectExceptionOnMethodCall('directory');
    }

    public function test_exception_on_is_file_when_file_was_externally_deleted(): void
    {
        $this->whenFileWasDeletedExpectExceptionOnMethodCall('isFile');
    }

    public function test_exception_on_is_directory_when_file_was_externally_deleted(): void
    {
        $this->whenFileWasDeletedExpectExceptionOnMethodCall('isDirectory');
    }

    public function test_exception_is_file_when_directory_was_externally_deleted(): void
    {
        $this->whenDirectoryWasDeletedExpectExceptionOnMethodCall('isFile');
    }

    public function test_exception_is_directory_when_directory_was_externally_deleted(): void
    {
        $this->whenDirectoryWasDeletedExpectExceptionOnMethodCall('isDirectory');
    }

    private function whenDirectoryWasDeletedExpectExceptionOnMethodCall(string $method): void
    {
        $name = 'the-subdirectory';
        $testDirectory = $this->testDirectory();
        $directory = $testDirectory->createDirectory($name);
        $testDirectory->deleteDirectory($name);

        $this->expectException(FilesystemException::class);
        $this->expectExceptionMessage('does not exist any more');

        $directory->$method();

        $this->cleanUpTestDirectory($testDirectory);
    }

    private function whenFileWasDeletedExpectExceptionOnMethodCall(string $method): void
    {
        $directory = $this->testDirectory();
        $file = $directory->createFile('the-filename', 'the-content');

        unlink($file->asString());

        $this->expectException(FilesystemException::class);
        $this->expectExceptionMessage('does not exist any more');

        $file->$method();

        $this->cleanUpTestDirectory($directory);
    }

    private function testDirectory(): ExistingDirectory
    {
        $temporaryDirectory = $this->temporaryDirectory();

        return ExistingDirectory::create($temporaryDirectory . '/' . uniqid('test-directory-', true));
    }

    private function cleanUpTestDirectory(ExistingDirectory $testDirectory): void
    {
        $testDirectory->deleteAllFilesAndDirectoriesRecursively();
    }

    private function temporaryDirectory(): string
    {
        $temporaryDirectory = sys_get_temp_dir();

        if (!is_writable($temporaryDirectory)) {
            $this->markTestSkipped('Cannot write to temporary directory');
        }

        return $temporaryDirectory;
    }
}
