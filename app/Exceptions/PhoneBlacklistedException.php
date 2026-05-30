<?php

namespace App\Exceptions;

use RuntimeException;

class PhoneBlacklistedException extends RuntimeException
{
    public function __construct(string $message = 'Nomor tidak dapat melakukan pemesanan')
    {
        parent::__construct($message);
    }
}
