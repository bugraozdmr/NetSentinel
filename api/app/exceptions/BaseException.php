<?php

namespace App\Exceptions;

class BaseException extends \Exception
{
    protected $statusCode;
    protected $errorCode;
    protected $data;

    public function __construct(string $message = "", int $statusCode = 500, string $errorCode = 'INTERNAL_ERROR', array $data = [])
    {
        parent::__construct($message);
        $this->statusCode = $statusCode;
        $this->errorCode = $errorCode;
        $this->data = $data;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function toArray(): array
    {
        return [
            'error' => true,
            'message' => $this->getMessage(),
            'code' => $this->getErrorCode(),
            'data' => $this->getData()
        ];
    }
} 