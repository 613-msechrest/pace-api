<?php

namespace Pace\RestServices;

use Pace\RestService;

class InvokeAction extends RestService
{
    /**
     * Invoke action.
     *
     * @param string $action
     * @param array $object
     * @param string|null $txnId
     * @return mixed
     */
    public function invoke($action, $object, $txnId = null)
    {
        $data = ['object' => $object];

        $params = [];

        if ($txnId !== null) {
            $params['txnId'] = $txnId;
        }

        $response = $this->http->post("/InvokeAction/{$action}", $data, $params);

        return $response;
    }
}
