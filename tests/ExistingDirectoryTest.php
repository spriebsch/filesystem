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

#[CoversClass(ExistingDirectory::class)]
#[CoversClass(FilesystemException::class)]
#[UsesClass(Filesystem::class)]
#[UsesClass(ExistingFile::class)]
class ExistingDirectoryTest extends AbstractDirectoryTestBase
{
    protected function directory(): Directory
    {
        $temporaryDirectory = sys_get_temp_dir();

        if (!is_writable($temporaryDirectory)) {
            $this->markTestSkipped(
                sprintf(
                    'Cannot write to temporary directory "%s"',
                    $temporaryDirectory
                )
            );
        }

        return ExistingDirectory::create(
            $temporaryDirectory . '/' . uniqid('test-directory-', true)
        );
    }

    public function test_can_be_created_from_path(): void
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

    public function test_directory_containing_a_file_is_not_empty(): void
    {
        $this->assertFalse(Filesystem::from(__DIR__)->isEmpty());
    }

    public function test_empty_directory_is_empty(): void
    {
        $directory = $this->directory();

        $this->assertTrue($directory->isEmpty());

        $this->cleanUpTestDirectory($directory);
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
        $this->assertEquals(
            __FILE__,
            Filesystem::from(__DIR__)->file(basename(__FILE__))->asString()
        );
    }

    public function test_can_check_file_existence(): void
    {
        $this->assertTrue(
            Filesystem::from(__DIR__)->exists(basename(__FILE__))
        );

        $this->assertFalse(
            Filesystem::from(__DIR__)->exists('does-not-exist')
        );
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

    protected function testDirectory(): Directory
    {
        $temporaryDirectory = $this->temporaryDirectory();

        return ExistingDirectory::create($temporaryDirectory . '/' . uniqid('test-directory-', true));
    }

    private function cleanUpTestDirectory(Directory $testDirectory): void
    {
        if ($testDirectory instanceof FakeDirectory) {
            return;
        }

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
