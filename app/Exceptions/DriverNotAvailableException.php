<?php

namespace App\Exceptions;

use RuntimeException;

class DriverNotAvailableException extends RuntimeException
{
    public function __construct(string $message = 'Driver tidak tersedia')
    {
        parent::__construct($message);
    }
}
