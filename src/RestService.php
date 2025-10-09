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
}
