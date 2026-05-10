<?php

namespace App\Exceptions;

class BusinessException extends \RuntimeException
{
    public function __construct(string $message, int $code = 400)
    {
        parent::__construct($message, $code);
    }
}
