<?php

namespace Pace\Contracts\Rest;

interface Factory
{
    /**
     * Create a new HTTP client instance.
     *
     * @param string $baseUrl
     * @return \Pace\Rest\HttpClient
     */
    public function make($baseUrl);

    /**
     * Set the specified HTTP client option.
     *
     * @param string $key
     * @param mixed $value
     */
    public function setOption($key, $value);

    /**
     * Bulk set the specified HTTP client options.
     *
     * @param array $options
     */
    public function setOptions(array $options);
}
