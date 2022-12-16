<?php

/*
 * This file is part of the zenstruck/temp-file package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
