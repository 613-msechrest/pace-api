<?php

namespace Pace;

use Pace\Rest\HttpClient;

abstract class RestService
{
    /**
     * The HTTP client instance.
     *
     * @var HttpClient
     */
    protected $http;

    /**
     * Create a new REST service instance.
     *
     * @param HttpClient $http
     */
    public function __construct(HttpClient $http)
    {
        $this->http = $http;
    }

    /**
     * Determine if debug logging should be enabled.
     *
     * @return bool
     */
    protected function shouldLogDebug()
    {
        // Check environment variable - works in Laravel and plain PHP
        $debug = getenv('PACE_API_DEBUG');
        if ($debug !== false) {
            return filter_var($debug, FILTER_VALIDATE_BOOLEAN);
        }

        // Fallback to Laravel's env() helper if available
        if (function_exists('env')) {
            return filter_var(env('PACE_API_DEBUG', false), FILTER_VALIDATE_BOOLEAN);
        }

        return false;
    }
}
