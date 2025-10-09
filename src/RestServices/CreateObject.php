<?php

namespace Pace\RestServices;

use Pace\RestService;

class CreateObject extends RestService
{
    /**
     * Create an object.
     *
     * @param string $object
     * @param array $attributes
     * @param string|null $txnId
     * @return array
     */
    public function create($object, array $attributes, $txnId = null)
    {
        $params = [];

        if ($txnId !== null) {
            $params['txnId'] = $txnId;
        }

        $response = $this->http->post("/CreateObject/create{$object}", $attributes, $params);

        return $response;
    }
}
