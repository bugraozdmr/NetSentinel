<?php
include_once __DIR__ . '/../data/serverInsertAreas.php';

class ServerValidator
{
    public static function validateInsert(array $data): array
    {
        $errors = [];

        if (empty($data['ip'])) {
            $errors['ip'] = 'IP address is required.';
        } elseif (!filter_var($data['ip'], FILTER_VALIDATE_IP)) {
            $errors['ip'] = 'Please provide a valid IP address.';
        }

        if (empty($data['name'])) {
            $errors['name'] = 'Server name is required.';
        }

        $validLocations = getLocations();
        if (!isset($data['location']) || strtolower($data['location']) === '' || !in_array(strtolower($data['location']), $validLocations, true)) {
            $errors['location'] = 'Location is required and must be one of the following values: ' . implode(', ', $validLocations);
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

        if (isset($data['ports'])) {
            if (!is_array($data['ports'])) {
                $errors['ports'] = 'Ports must be an array.';
            } else {
                foreach ($data['ports'] as $index => $port) {
                    $portNumber = is_array($port) ? ($port['port_number'] ?? null) : $port;

                    if (!is_numeric($portNumber)) {
                        $errors["ports[$index]"] = 'Port number must be a numeric value.';
                    } elseif ($portNumber < 1 || $portNumber > 65535) {
                        $errors["ports[$index]"] = 'Port number must be between 1 and 65535.';
                    }
                }
            }
        }

        $validPanels = getPanels();
        if (!isset($data['panel']) || strtolower($data['panel']) === '' || !in_array(strtolower($data['panel']), array_map('strtolower', $validPanels), true)) {
            $errors['panel'] = 'Panel area is required and must be one of the following values: ' . implode(', ', $validPanels);
        }

        return $errors;
    }

    public static function validateUpdate(array $data): array
    {
        $errors = [];

        if (!isset($data['ip']) || empty($data['ip'])) {
            $errors['ip'] = 'IP address is required.';
        } elseif (!filter_var($data['ip'], FILTER_VALIDATE_IP)) {
            $errors['ip'] = 'Please provide a valid IP address.';
        }

        if (!isset($data['name']) || empty($data['name'])) {
            $errors['name'] = 'Server name is required.';
        }

        $validLocations = getLocations();
        if (!isset($data['location']) || strtolower($data['location']) === '' || !in_array(strtolower($data['location']), $validLocations, true)) {
            $errors['location'] = 'Location is required and must be one of the following values: ' . implode(', ', $validLocations);
        }

        if (isset($data['ports'])) {
            if (!is_array($data['ports'])) {
                $errors['ports'] = 'Ports must be an array.';
            } else {
                foreach ($data['ports'] as $index => $port) {
                    $portNumber = is_array($port) ? ($port['port_number'] ?? null) : $port;

                    if (!is_numeric($portNumber)) {
                        $errors["ports[$index]"] = 'Port number must be a numeric value.';
                    } elseif ($portNumber < 1 || $portNumber > 65535) {
                        $errors["ports[$index]"] = 'Port number must be between 1 and 65535.';
                    }
                }
            }
        }

        $validPanels = getPanels();
        if (isset($data['panel'])) {
            if (!in_array(strtolower($data['panel']), array_map('strtolower', $validPanels), true)) {
                $errors['panel'] = 'Panel must be one of the following values: ' . implode(', ', $validPanels);
            }
        }

        return $errors;
    }
}
