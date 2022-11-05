<?php

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
