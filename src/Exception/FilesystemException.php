<?php

declare(strict_types=1);

namespace Yokai\Batch\Exception;

class FilesystemException extends RuntimeException
{
    public static function cannotCreateDir(string $path): self
    {
        return new self(sprintf('Cannot create dir "%s".', $path));
    }

    public static function cannotReadFile(string $path): self
    {
        return new self(sprintf('Cannot read "%s" file content.', $path));
    }

    public static function cannotWriteFile(string $path): self
    {
        return new self(sprintf('Cannot write content to file "%s".', $path));
    }

    public static function cannotRemoveFile(string $path): self
    {
        return new self(sprintf('Unable to remove file "%s".', $path));
    }

    public static function fileNotFound(string $path): self
    {
        return new self(sprintf('File "%s" does not exists.', $path));
    }
}
