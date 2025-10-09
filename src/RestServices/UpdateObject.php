<?php

namespace Pace\RestServices;

use Pace\RestService;

class UpdateObject extends RestService
{
    /**
     * Update an object.
     *
     * @param string $object
     * @param array $attributes
     * @param string|null $txnId
     * @return array
     */
    public function update($object, $attributes, $txnId = null)
    {
        $params = [];

        if ($txnId !== null) {
            $params['txnId'] = $txnId;
        }

        $response = $this->http->put("/UpdateObject/update{$object}", $attributes, $params);

        return $response;
    }
}
