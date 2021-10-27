<?php

namespace App\Tests\Support;

use Zenstruck\Mailer\Test\TestEmail;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ResetPasswordEmail extends TestEmail
{
    public function resetLink(): string
    {
        preg_match('#localhost(.+)#', $this->getTextBody(), $matches);

        return $matches[1];
    }
}
