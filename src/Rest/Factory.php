<?php

namespace Pace\Rest;

use Pace\Contracts\Rest\Factory as FactoryContract;

class Factory implements FactoryContract
{
    /**
     * HTTP client options.
     *
     * @var array
     */
    protected $options = [];

    /**
     * Create a new HttpClient instance.
     *
     * @param string $baseUrl
     * @return HttpClient
     */
    public function make($baseUrl)
    {
        return new HttpClient($baseUrl, $this->getOptions());
    }

    /**
     * Set the specified HTTP client option.
     *
     * @param string $key
     * @param mixed $value
     */
    public function setOption($key, $value)
    {
        $this->options[$key] = $value;
    }

    /**
     * Bulk set the specified HTTP client options.
     *
     * @param array $options
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            $this->setOption($key, $value);
        }
    }

    /**
     * Get the HTTP client options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array_merge($this->options, [
            'timeout' => 30,
            'verify' => true,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);
    }
}
