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

#[CoversClass(FilesystemException::class)]
class FilesystemExceptionTest extends TestCase
{
    public function test_does_not_exist(): void
    {
        $exception = FilesystemException::doesNotExist('the-filename');

        $this->assertStringContainsString('does not exist', $exception->getMessage());
    }

    public function test_does_not_exist_contains_filename(): void
    {
        $exception = FilesystemException::doesNotExist('the-filename');

        $this->assertStringContainsString('the-filename', $exception->getMessage());
    }

    public function test_directory_does_not_exist(): void
    {
        $exception = FilesystemException::directoryDoesNotExist('the-filename');

        $this->assertStringContainsString('does not exist', $exception->getMessage());
    }

    public function test_directory_does_not_exist_contains_filename(): void
    {
        $exception = FilesystemException::directoryDoesNotExist('the-filename');

        $this->assertStringContainsString('the-filename', $exception->getMessage());
    }

    public function test_file_does_not_exist(): void
    {
        $exception = FilesystemException::fileDoesNotExist('the-filename');

        $this->assertStringContainsString('does not exist', $exception->getMessage());
    }

    public function test_file_does_not_exist_contains_filename(): void
    {
        $exception = FilesystemException::fileDoesNotExist('the-filename');

        $this->assertStringContainsString('the-filename', $exception->getMessage());
    }

    public function test_file_exists(): void
    {
        $exception = FilesystemException::fileExists('the-filename');

        $this->assertStringContainsString('exists', $exception->getMessage());
    }

    public function test_file_exists_contains_filename(): void
    {
        $exception = FilesystemException::fileExists('the-filename');

        $this->assertStringContainsString('the-filename', $exception->getMessage());
    }

    public function test_failed_to_create(): void
    {
        $exception = FilesystemException::failedToCreateDirectory('the-filename');

        $this->assertStringContainsString('Failed to create', $exception->getMessage());
    }

    public function test_failed_to_create_contains_filename(): void
    {
        $exception = FilesystemException::failedToCreateDirectory('the-filename');

        $this->assertStringContainsString('the-filename', $exception->getMessage());
    }
}
