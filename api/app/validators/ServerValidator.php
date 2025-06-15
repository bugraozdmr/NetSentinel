<?php

class ServerValidator {
    public static function validateInsert(array $data): array {
        $errors = [];

        if (empty($data['ip'])) {
            $errors['ip'] = 'IP address is required.';
        } elseif (!filter_var($data['ip'], FILTER_VALIDATE_IP)) {
            $errors['ip'] = 'Please provide a valid IP address.';
        }

        if (empty($data['name'])) {
            $errors['name'] = 'Server name is required.';
        }
        
        if (!isset($data['location']) || empty($data['location'])) {
            $errors['location'] = 'Location is required.';
        }

        if (empty($data['assigned_id'])) {
            $errors['assigned_id'] = 'Assigned ID is required.';
        }

        if (isset($data['is_active']) && !is_bool($data['is_active'])) {
            $errors['is_active'] = 'The is_active field must be a boolean if provided.';
        }

        if (isset($data['last_checks'])) {
            json_decode($data['last_checks']);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $errors['last_checks'] = 'last_checks must be a valid JSON string.';
            }
        }

        return $errors;
    }

    public static function validateUpdate(array $data): array {
        $errors = [];

        if (!isset($data['ip']) || empty($data['ip'])) {
            $errors['ip'] = 'IP address is required.';
        } elseif (!filter_var($data['ip'], FILTER_VALIDATE_IP)) {
            $errors['ip'] = 'Please provide a valid IP address.';
        }

        if (!isset($data['name']) || empty($data['name'])) {
            $errors['name'] = 'Server name is required.';
        }

        if (!isset($data['location']) || empty($data['location'])) {
            $errors['location'] = 'Location is required.';
        }

        if (!isset($data['assigned_id']) || empty($data['assigned_id'])) {
            $errors['assigned_id'] = 'Assigned ID is required.';
        }

        return $errors;
    }
}
