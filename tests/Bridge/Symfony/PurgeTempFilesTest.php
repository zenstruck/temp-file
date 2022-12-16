<?php

/*
 * This file is part of the zenstruck/temp-file package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Tests\Bridge\Symfony;

use PHPUnit\Framework\TestCase;
use Zenstruck\TempFile;
use Zenstruck\TempFile\Bridge\Symfony\PurgeTempFiles;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class PurgeTempFilesTest extends TestCase
{
    /**
     * @test
     */
    public function reset(): void
    {
        $file = TempFile::new();

        $this->assertFileExists($file);

        (new PurgeTempFiles())->reset();

        $this->assertFileDoesNotExist($file);
    }
}
