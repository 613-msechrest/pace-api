<?php

// Load autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load .env file using vlucas/phpdotenv
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Laravel-style env() helper function
if (!function_exists('env')) {
    function env($key, $default = null) {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? $default;

        if ($value === null) {
            return $default;
        }

        // Convert string values
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return null;
        }

        return $value;
    }
}

return [
    'host' => env('PACE_HOST'),
    'login' => env('PACE_LOGIN'),
    'password' => env('PACE_PASSWORD'),
    'scheme' => env('PACE_SCHEME', 'https'),
    'protocol' => env('PACE_PROTOCOL', 'rest'),
    'timeout' => env('PACE_TEST_TIMEOUT', 30),
    'verify_ssl' => env('PACE_TEST_VERIFY_SSL', true),
];
