<?php

class PortValidator {
    public static function validateInsert(array $data): array {
        $errors = [];

        if (empty($data['server_id'])) {
            $errors['server_id'] = 'Server ID is required.';
        } elseif (!is_numeric($data['server_id'])) {
            $errors['server_id'] = 'Server ID must be a number.';
        }

        if (!isset($data['port_number'])) {
            $errors['port_number'] = 'Port number is required.';
        } elseif (!is_numeric($data['port_number']) || $data['port_number'] < 1 || $data['port_number'] > 65535) {
            $errors['port_number'] = 'Port number must be a valid integer between 1 and 65535.';
        }

        if (isset($data['is_open']) && !is_bool($data['is_open'])) {
            $errors['is_open'] = 'The is_open field must be a boolean if provided.';
        }

        return $errors;
    }
}
