<?php

/*
 * This file is part of the zenstruck/temp-file package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Tests;

use PHPUnit\Framework\TestCase;
use Zenstruck\TempFile;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class TempFileTest extends TestCase
{
    /**
     * @test
     */
    public function can_create_for_existing_file(): void
    {
        $file = TempFile::new(\sys_get_temp_dir().'/zs'.__METHOD__);

        $this->assertFileDoesNotExist($file);

        \file_put_contents($file, 'contents');

        $this->assertStringEqualsFile($file, 'contents');
    }

    /**
     * @test
     */
    public function can_create_for_spl_file(): void
    {
        $file = TempFile::for(new \SplFileInfo(__FILE__));

        $this->assertSame(\file_get_contents(__FILE__), $file->contents());
        $this->assertSame('0664', \mb_substr(\sprintf('%o', \fileperms($file)), -4));
    }

    /**
     * @test
     */
    public function can_create_with_extension(): void
    {
        $file = TempFile::withExtension('gif');

        $this->assertFileExists($file);
        $this->assertStringEndsWith('.gif', (string) $file);
        $this->assertFileDoesNotExist(\mb_substr($file, 0, -4));
        $this->assertSame('0664', \mb_substr(\sprintf('%o', \fileperms($file)), -4));
    }

    /**
     * @test
     */
    public function exists_when_created(): void
    {
        $this->assertFileExists($file = new TempFile());
        $this->assertSame('0664', \mb_substr(\sprintf('%o', \fileperms($file)), -4));
    }

    /**
     * @test
     */
    public function can_delete(): void
    {
        \file_put_contents($file = new TempFile(), 'contents');

        $this->assertFileExists($file);

        $file->delete();
        $file->delete();

        $this->assertFileDoesNotExist($file);
    }

    /**
     * @test
     */
    public function cannot_create_for_directory(): void
    {
        $this->expectException(\LogicException::class);

        new TempFile(__DIR__);
    }

    /**
     * @test
     */
    public function can_create_for_stream(): void
    {
        $resource = \fopen('php://memory', 'rw');
        \fwrite($resource, 'file contents');
        \rewind($resource);

        $file = TempFile::for($resource);

        \fclose($resource);

        $this->assertFileExists($file);
        $this->assertStringEqualsFile($file, 'file contents');
        $this->assertSame('0664', \mb_substr(\sprintf('%o', \fileperms($file)), -4));
    }

    /**
     * @test
     */
    public function can_create_for_string(): void
    {
        $file = TempFile::for('file contents');

        $this->assertFileExists($file);
        $this->assertStringEqualsFile($file, 'file contents');
        $this->assertSame('0664', \mb_substr(\sprintf('%o', \fileperms($file)), -4));
    }

    /**
     * @test
     */
    public function can_refresh(): void
    {
        $file = TempFile::for('foobar');

        $this->assertSame(6, $file->getSize());

        \file_put_contents($file, 'foobarbaz');

        $this->assertSame(6, $file->getSize());
        $this->assertSame(9, $file->refresh()->getSize());
    }

    /**
     * @test
     */
    public function can_purge_created_files(): void
    {
        $file1 = TempFile::for('contents');
        $file2 = TempFile::for('contents');

        $this->assertFileExists($file1);
        $this->assertFileExists($file2);

        TempFile::purge();

        $this->assertFileDoesNotExist($file1);
        $this->assertFileDoesNotExist($file2);
    }

    /**
     * @test
     */
    public function default_create_image_is_jpg(): void
    {
        $imageSize = \getimagesize($file = TempFile::image());

        $this->assertSame(10, $imageSize[0]);
        $this->assertSame(10, $imageSize[1]);
        $this->assertSame('image/jpeg', $imageSize['mime']);
        $this->assertSame('jpg', $file->getExtension());
        $this->assertSame('0664', \mb_substr(\sprintf('%o', \fileperms($file)), -4));
    }

    /**
     * @test
     * @dataProvider imageTypeProvider
     */
    public function can_create_image_for_type(string $type, string $expectedMime): void
    {
        $imageSize = \getimagesize($file = TempFile::image(type: $type));

        $this->assertSame(10, $imageSize[0]);
        $this->assertSame(10, $imageSize[1]);
        $this->assertSame($expectedMime, $imageSize['mime']);
        $this->assertSame($type, $file->getExtension());
        $this->assertSame('0664', \mb_substr(\sprintf('%o', \fileperms($file)), -4));
    }

    public static function imageTypeProvider(): iterable
    {
        yield ['jpg', 'image/jpeg'];
        yield ['jpeg', 'image/jpeg'];
        yield ['gif', 'image/gif'];
        yield ['bmp', 'image/bmp'];
        yield ['webp', 'image/webp'];
        yield ['wbmp', 'image/vnd.wap.wbmp'];
    }

    /**
     * @test
     */
    public function can_create_image_with_dimensions(): void
    {
        $imageSize = \getimagesize($file = TempFile::image(5, 6, 'PNG'));

        $this->assertSame(5, $imageSize[0]);
        $this->assertSame(6, $imageSize[1]);
        $this->assertSame('image/png', $imageSize['mime']);
        $this->assertSame('png', $file->getExtension());
        $this->assertSame('0664', \mb_substr(\sprintf('%o', \fileperms($file)), -4));
    }

    /**
     * @test
     */
    public function can_create_image_with_name(): void
    {
        $imageSize = \getimagesize($file = TempFile::image(5, 6, name: 'some-image.png'));

        $this->assertFileExists($file);
        $this->assertSame(\sys_get_temp_dir().'/some-image.png', (string) $file);
        $this->assertSame(5, $imageSize[0]);
        $this->assertSame(6, $imageSize[1]);
        $this->assertSame('image/png', $imageSize['mime']);
        $this->assertSame('png', $file->getExtension());
        $this->assertSame('0664', \mb_substr(\sprintf('%o', \fileperms($file)), -4));
    }

    /**
     * @test
     */
    public function image_name_requires_extension(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        TempFile::image(name: 'some-image');
    }

    /**
     * @test
     */
    public function image_name_cannot_contain_directory_separator(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        TempFile::image(name: 'some/dir/image.png');
    }

    /**
     * @test
     */
    public function cannot_create_image_for_invalid_type(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        TempFile::image(type: 'invalid');
    }

    /**
     * @test
     */
    public function can_create_named_temp_file(): void
    {
        $file = TempFile::withName('some-file.txt');

        $this->assertFileExists($file);
        $this->assertSame(\sys_get_temp_dir().'/some-file.txt', (string) $file);
        $this->assertSame('', \file_get_contents($file));
        $this->assertSame('0664', \mb_substr(\sprintf('%o', \fileperms($file)), -4));

        TempFile::purge();

        $this->assertFileDoesNotExist($file);
    }

    /**
     * @test
     */
    public function can_create_named_temp_file_with_string_content(): void
    {
        $file = TempFile::withName('some-file.txt', 'content');

        $this->assertFileExists($file);
        $this->assertSame(\sys_get_temp_dir().'/some-file.txt', (string) $file);
        $this->assertSame('content', \file_get_contents($file));
        $this->assertSame('0664', \mb_substr(\sprintf('%o', \fileperms($file)), -4));

        TempFile::purge();

        $this->assertFileDoesNotExist($file);
    }

    /**
     * @test
     */
    public function can_create_named_temp_file_with_spl_file(): void
    {
        $file = TempFile::withName('some-file.txt', new \SplFileInfo(__FILE__));

        $this->assertFileExists($file);
        $this->assertSame(\sys_get_temp_dir().'/some-file.txt', (string) $file);
        $this->assertFileEquals($file, __FILE__);
        $this->assertSame('0664', \mb_substr(\sprintf('%o', \fileperms($file)), -4));

        TempFile::purge();

        $this->assertFileDoesNotExist($file);
    }

    /**
     * @test
     */
    public function name_cannot_contain_directory_separator(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        TempFile::withName('some/dir/some-file.txt');
    }
}
