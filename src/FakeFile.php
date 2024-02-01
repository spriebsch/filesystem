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

use Exception;

final class FakeFile implements File
{
    public function __construct(
        private string $filename,
        private string $content,
        private mixed  $require = null
    ) {}

    public function isFile(): bool
    {
        return true;
    }

    public function isDirectory(): bool
    {
        return false;
    }

    public function directory(): Directory
    {
        return new FakeDirectory(dirname($this->filename));
    }

    public function require(): mixed
    {
        if ($this->require !== null) {
            return $this->require;
        }

        if ($this->content !== null) {
            return eval(
            substr(
                $this->content,
                strlen(
                    '<?php declare(strict_types=1);'
                )
            )
            );
        }

        // @todo exception?

        return null;
    }

    public function load(): string
    {
        return $this->content;
    }

    public function asString(): string
    {
        return $this->filename;
    }
}
