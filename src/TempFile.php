<?php

/*
 * This file is part of the zenstruck/temp-file package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class TempFile extends \SplFileInfo
{
    /** @var string[] */
    private static array $created = [];

    public function __construct(?string $filename = null)
    {
        $filename ??= self::tempFile();

        if (\is_dir($filename)) {
            throw new \LogicException("\"{$filename}\" is a directory.");
        }

        parent::__construct($filename);

        if (!self::$created) {
            // delete on script end
            \register_shutdown_function([self::class, 'purge']);
        }

        self::$created[] = $filename;
    }

    public static function new(?string $filename = null): self
    {
        return new self($filename);
    }

    public static function withExtension(string $extension): self
    {
        $original = self::tempFile();

        if (!@\rename($original, $new = "{$original}.{$extension}")) {
            throw new \RuntimeException('Unable to create temp file with extension.');
        }

        return new self($new);
    }

    /**
     * @param string|resource|\SplFileInfo $what
     */
    public static function for(mixed $what, ?string $extension = null): self
    {
        $file = $extension ? self::withExtension($extension) : new self();

        if ($what instanceof \SplFileInfo) {
            @\copy($what, $file) ?: throw new \RuntimeException('Unable to copy file.');

            return $file->refresh();
        }

        if (false === @\file_put_contents($file, $what)) {
            throw new \RuntimeException('Unable to write to file.');
        }

        return $file->refresh();
    }

    /**
     * Create temporary image file.
     *
     * @source https://github.com/laravel/framework/blob/183d38f18c0ea9fe13b6d10a6d8360be881d096c/src/Illuminate/Http/Testing/FileFactory.php#L68
     */
    public static function image(int $width = 10, int $height = 10, string $type = 'jpg'): self
    {
        $type = \mb_strtolower($type);
        $file = self::withExtension($type);

        if (false === $image = @\imagecreatetruecolor($width, $height)) {
            throw new \RuntimeException('Error creating temporary image.');
        }

        $ret = match ($type) {
            'jpeg', 'jpg' => @\imagejpeg($image, (string) $file),
            'png' => @\imagepng($image, (string) $file),
            'gif' => @\imagegif($image, (string) $file),
            'bmp' => @\imagebmp($image, (string) $file),
            'webp' => @\imagewebp($image, (string) $file),
            'wbmp' => @\imagewbmp($image, (string) $file),
            default => throw new \InvalidArgumentException(\sprintf('"%s" is an invalid image type.', $type)),
        };

        if (false === $ret) {
            throw new \RuntimeException('Error creating temporary image.');
        }

        return $file->refresh();
    }

    /**
     * Manually delete all created temp files. Useful for long-running
     * processes.
     */
    public static function purge(): void
    {
        foreach (self::$created as $filename) {
            if (\file_exists($filename)) {
                \unlink($filename);
            }
        }

        self::$created = [];
    }

    public function contents(): string
    {
        return \file_get_contents($this) ?: throw new \RuntimeException('Unable to get file contents.');
    }

    public function refresh(): self
    {
        \clearstatcache(false, $this);

        return $this;
    }

    public function delete(): self
    {
        if (\file_exists($this)) {
            \unlink($this);
        }

        return $this;
    }

    private static function tempFile(): string
    {
        if (false === $filename = @\tempnam(\sys_get_temp_dir(), 'zstf_')) {
            throw new \RuntimeException('Failed to create temporary file.');
        }

        return $filename;
    }
}
