<?php

namespace App\Core;

use App\Exceptions\BaseException;
use App\Exceptions\ValidationException;
use App\Exceptions\NotFoundException;
use App\Exceptions\DatabaseException;
use PDOException;

class ExceptionHandler
{
    private static $logger;

    public static function handle($exception)
    {
        if (!self::$logger) {
            self::$logger = Logger::getInstance();
        }

        // Log the exception
        self::logException($exception);

        // Handle our custom exceptions
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

    private static function logException($exception): void
    {
        $level = 'error';
        $context = [
            'exception_class' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ];

        // Add specific context for different exception types
        if ($exception instanceof ValidationException) {
            $level = 'warning';
            $context['validation_errors'] = $exception->getData();
        } elseif ($exception instanceof NotFoundException) {
            $level = 'notice';
            $context['resource_id'] = $exception->getData();
        } elseif ($exception instanceof DatabaseException) {
            $level = 'error';
            $context['database_details'] = $exception->getData();
        } elseif ($exception instanceof PDOException) {
            $level = 'critical';
            $context['pdo_code'] = $exception->getCode();
        }

        self::$logger->$level($exception->getMessage(), $context);
    }
} 