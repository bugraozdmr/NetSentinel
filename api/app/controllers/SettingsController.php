<?php

use App\Exceptions\ValidationException;

class SettingsController
{
    private $settingsFile;
    private $configFile;

    public function __construct()
    {
        $this->settingsFile = __DIR__ . '/../../data/settings.json';
        $this->configFile = __DIR__ . '/../../../frontend/assets/js/config.js';
    }

    public function save($data = [])
    {
        try {
            if (empty($data)) {
                $data = json_decode(file_get_contents("php://input"), true);
            }
            
            // Debug logging
            error_log("Settings save - received data: " . json_encode($data));
            
            // Validate required fields
            $this->validateSettings($data);
            
            // Save to JSON file
            $success = $this->saveSettingsToFile($data);
            
            if ($success) {
                return [
                    'success' => true,
                    'message' => 'Ayarlar başarıyla kaydedildi',
                    'data' => $data
                ];
            } else {
                http_response_code(500);
                return [
                    'success' => false,
                    'message' => 'Ayarlar kaydedilirken hata oluştu'
                ];
            }
            
        } catch (ValidationException $e) {
            http_response_code(400);
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e->getErrors()
            ];
        } catch (\Exception $e) {
            http_response_code(500);
            return [
                'success' => false,
                'message' => 'Beklenmeyen bir hata oluştu: ' . $e->getMessage()
            ];
        }
    }

    public function get($data = [])
    {
        try {
            $settings = $this->loadSettingsFromFile();
            
            return [
                'success' => true,
                'data' => $settings
            ];
            
        } catch (\Exception $e) {
            http_response_code(500);
            return [
                'success' => false,
                'message' => 'Ayarlar yüklenirken hata oluştu: ' . $e->getMessage()
            ];
        }
    }

    public function updateConfig($data = [])
    {
        try {
            if (empty($data)) {
                $data = json_decode(file_get_contents("php://input"), true);
            }
            
            if (empty($data['configContent'])) {
                throw new ValidationException('Config içeriği gereklidir');
            }
            
            // Update config.js file
            $success = $this->updateConfigFile($data['configContent']);
            
            if ($success) {
                return [
                    'success' => true,
                    'message' => 'Config dosyası başarıyla güncellendi'
                ];
            } else {
                http_response_code(500);
                return [
                    'success' => false,
                    'message' => 'Config dosyası güncellenirken hata oluştu'
                ];
            }
            
        } catch (ValidationException $e) {
            http_response_code(400);
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        } catch (\Exception $e) {
            http_response_code(500);
            return [
                'success' => false,
                'message' => 'Beklenmeyen bir hata oluştu: ' . $e->getMessage()
            ];
        }
    }

    private function validateSettings($data)
    {
        $errors = [];

        // Required fields
        if (empty($data['apiBaseUrl'])) {
            $errors['apiBaseUrl'] = 'API Base URL gereklidir';
        } elseif (!filter_var($data['apiBaseUrl'], FILTER_VALIDATE_URL)) {
            $errors['apiBaseUrl'] = 'Geçerli bir URL giriniz';
        }

        if (empty($data['appName'])) {
            $errors['appName'] = 'Uygulama adı gereklidir';
        }

        if (empty($data['updateInterval'])) {
            $errors['updateInterval'] = 'Güncelleme aralığı gereklidir';
        } elseif (!is_numeric($data['updateInterval']) || $data['updateInterval'] < 5 || $data['updateInterval'] > 3600) {
            $errors['updateInterval'] = 'Güncelleme aralığı 5-3600 saniye arasında olmalıdır';
        }

        if (empty($data['updateMode'])) {
            $errors['updateMode'] = 'Güncelleme modu gereklidir';
        } elseif (!in_array($data['updateMode'], ['page_refresh', 'real_time'])) {
            $errors['updateMode'] = 'Geçersiz güncelleme modu';
        }

        if (!empty($errors)) {
            throw new ValidationException('Ayar doğrulama hatası', $errors);
        }
    }

    private function saveSettingsToFile($data)
    {
        try {
            // Ensure data directory exists
            $dataDir = dirname($this->settingsFile);
            if (!is_dir($dataDir)) {
                mkdir($dataDir, 0755, true);
            }

            // Prepare settings with defaults
            $settings = [
                'apiBaseUrl' => $data['apiBaseUrl'],
                'appName' => $data['appName'],
                'updateMode' => $data['updateMode'],
                'updateInterval' => (int) $data['updateInterval'],
                'timezone' => $data['timezone'] ?? 'Europe/Istanbul',
                'language' => $data['language'] ?? 'tr',
                'updatedAt' => date('Y-m-d H:i:s')
            ];

            // Write to file
            $jsonData = json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            $result = file_put_contents($this->settingsFile, $jsonData);

            return $result !== false;
            
        } catch (\Exception $e) {
            error_log("Settings save error: " . $e->getMessage());
            return false;
        }
    }

    private function updateConfigFile($configContent)
    {
        try {
            // Ensure config file directory exists
            $configDir = dirname($this->configFile);
            if (!is_dir($configDir)) {
                mkdir($configDir, 0755, true);
            }

            // Write config content to file
            $result = file_put_contents($this->configFile, $configContent);

            return $result !== false;
            
        } catch (\Exception $e) {
            error_log("Config file update error: " . $e->getMessage());
            return false;
        }
    }

    private function loadSettingsFromFile()
    {
        if (!file_exists($this->settingsFile)) {
            return $this->getDefaultSettings();
        }

        try {
            $jsonData = file_get_contents($this->settingsFile);
            $settings = json_decode($jsonData, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('JSON parse error: ' . json_last_error_msg());
            }

            return $settings;
            
        } catch (\Exception $e) {
            error_log("Settings load error: " . $e->getMessage());
            return $this->getDefaultSettings();
        }
    }

    private function getDefaultSettings()
    {
        return [
            'apiBaseUrl' => 'http://192.168.1.34/netsentinel/api',
            'appName' => 'netsentinel',
            'updateMode' => 'page_refresh',
            'updateInterval' => 300,
            'timezone' => 'Europe/Istanbul',
            'language' => 'tr',
            'updatedAt' => date('Y-m-d H:i:s')
        ];
    }
} 