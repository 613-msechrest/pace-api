<?php

namespace Pace\RestServices;

use Pace\RestService;

class InvokePaceConnect extends RestService
{
    /**
     * Invoke a Pace Connect process.
     *
     * @param string $process
     * @param array $parameters
     * @param string|null $txnId
     * @return mixed
     */
    public function invoke($process, $parameters, $txnId = null)
    {
        $data = ['parameters' => $parameters];

        $params = [];

        if ($txnId !== null) {
            $params['txnId'] = $txnId;
        }

        $response = $this->http->post("/InvokePaceConnect/{$process}", $data, $params);

        return $response;
    }
}
