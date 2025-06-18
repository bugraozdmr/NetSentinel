<?php

namespace App\Exceptions;

class DatabaseException extends BaseException
{
    public function __construct(string $message = "Database error occurred", array $data = [])
    {
        parent::__construct($message, 500, 'DATABASE_ERROR', $data);
    }
} 