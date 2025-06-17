<?php

class NotificationValidator
{
    public static function validateInsert(array $data): array {
        $errors = [];

        if (empty($data['server_id'])) {
            $errors['server_id'] = 'Server ID is required.';
        } elseif (!is_numeric($data['server_id'])) {
            $errors['server_id'] = 'Server ID must be a number.';
        }

        if (empty($data['message'])) {
            $errors['message'] = 'Message is required.';
        } elseif (!is_string($data['message']) || trim($data['message']) === '') {
            $errors['message'] = 'Message must be a non-empty string.';
        }

        return $errors;
    }
}
