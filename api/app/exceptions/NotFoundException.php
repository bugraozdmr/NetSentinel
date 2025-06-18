<?php

namespace App\Exceptions;

class NotFoundException extends BaseException
{
    public function __construct(string $message = "Resource not found", array $data = [])
    {
        parent::__construct($message, 404, 'NOT_FOUND', $data);
    }
} 