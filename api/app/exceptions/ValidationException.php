<?php

namespace App\Exceptions;

class ValidationException extends BaseException
{
    public function __construct(string $message = "Validation failed", array $data = [])
    {
        parent::__construct($message, 400, 'VALIDATION_ERROR', $data);
    }
} 