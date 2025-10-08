<?php

namespace Pace\Services;

use Pace\Service;

class InvokeProcess extends Service
{
    /**
     * Invoke process.
     *
     * @param string $process
     * @param array $parameters Array of XPath expressions for object identification
     * @return mixed
     */
    public function invokeProcess($process, $parameters)
    {
        $request = [];

        // Build request array from parameters array
        // Parameters should be XPath expressions for object identification
        foreach ($parameters as $index => $parameter) {
            $request["in{$index}"] = $parameter;
        }

        $response = $this->soap->{$process}($request);

        return $response->out;
    }
}