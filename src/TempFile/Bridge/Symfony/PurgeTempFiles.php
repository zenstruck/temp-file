<?php

namespace Zenstruck\TempFile\Bridge\Symfony;

use Symfony\Contracts\Service\ResetInterface;
use Zenstruck\TempFile;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class PurgeTempFiles implements ResetInterface
{
    public function reset(): void
    {
        TempFile::purge();
    }
}
