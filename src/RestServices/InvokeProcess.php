<?php

namespace Pace\RestServices;

use Pace\RestService;

class InvokeProcess extends RestService
{
    /**
     * Invoke process.
     *
     * @param string $process
     * @param array $parameters Array of XPath expressions for object identification
     * @param string|null $txnId
     * @return mixed
     */
    public function invoke($process, $parameters, $txnId = null)
    {
        $data = [];

        // Build request array from parameters array
        // Parameters should be XPath expressions for object identification
        foreach ($parameters as $index => $parameter) {
            $data["in{$index}"] = $parameter;
        }

        $params = [];

        if ($txnId !== null) {
            $params['txnId'] = $txnId;
        }

        $response = $this->http->post("/InvokeProcess/{$process}", $data, $params);

        return $response;
    }
}
