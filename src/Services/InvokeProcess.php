<?php

namespace Pace\Services;

use Pace\Service;

class InvokeProcess extends Service
{
    /**
     * Invoke process.
     *
     * @param string $process
     * @param array $parameters
     * @return mixed
     */
    public function invokeProcess($process, ...$parameters)
    {
	    $request = [];

        foreach ($parameters as $index => $parameter) {
            $request["in{$index}"] = $parameter;
        }

        $response = $this->soap->{$process}($request);

        return $response->out;
    }
}