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

        // Extract primary key for query parameters
        $keyName = \Pace\Type::keyName($object) ?: 'primaryKey';
        $key = $attributes[$keyName] ?? $attributes['primaryKey'] ?? null;
        if ($key) {
            $params['primaryKey'] = $key;
            $params[$keyName] = $key;
        }

        return $this->http->post("UpdateObject/update{$object}", $attributes, $params);
    }
}

