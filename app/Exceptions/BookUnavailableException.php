<?php

namespace App\Exceptions;

use Exception;

class BookUnavailableException extends Exception
{
    public function __construct(string $message = 'Book is not available.')
    {
        parent::__construct($message);
    }
}
