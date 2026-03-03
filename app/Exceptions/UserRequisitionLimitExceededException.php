<?php

namespace App\Exceptions;

use Exception;

class UserRequisitionLimitExceededException extends Exception
{
    public function __construct(string $message = 'You already have 3 active requisitions.')
    {
        parent::__construct($message);
    }
}
