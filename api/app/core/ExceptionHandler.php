<?php

namespace App\Core;

use App\Exceptions\BaseException;
use App\Exceptions\ValidationException;
use App\Exceptions\NotFoundException;
use App\Exceptions\DatabaseException;
use PDOException;

class ExceptionHandler
{
    public static function handle($exception)
    {
        error_log($exception->getMessage() . "\n" . $exception->getTraceAsString());

        if ($exception instanceof ValidationException) {
            http_response_code(400);
            return json_encode([
                'error' => true,
                'message' => $exception->getMessage(),
                'code' => 'VALIDATION_ERROR',
                'data' => $exception->getData()
            ]);
        }

        if ($exception instanceof NotFoundException) {
            http_response_code(404);
            return json_encode([
                'error' => true,
                'message' => $exception->getMessage(),
                'code' => 'NOT_FOUND',
                'data' => $exception->getData()
            ]);
        }

        if ($exception instanceof DatabaseException) {
            http_response_code(500);
            return json_encode([
                'error' => true,
                'message' => $exception->getMessage(),
                'code' => 'DATABASE_ERROR',
                'data' => $exception->getData()
            ]);
        }

        if ($exception instanceof BaseException) {
            http_response_code($exception->getStatusCode());
            return json_encode($exception->toArray());
        }

        if ($exception instanceof PDOException) {
            http_response_code(500);
            return json_encode([
                'error' => true,
                'message' => 'Database error occurred',
                'code' => 'DATABASE_ERROR',
                'data' => []
            ]);
        }

        // Handle any other exceptions
        http_response_code(500);
        return json_encode([
            'error' => true,
            'message' => 'Internal server error',
            'code' => 'INTERNAL_ERROR',
            'data' => []
        ]);
    }
} 