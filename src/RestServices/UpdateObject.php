<?php

namespace Pace\RestServices;

use Pace\RestService;
use Pace\Type;

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

        $key = $this->resolveObjectKey($object, $attributes);

        if ($key !== null) {
            $keyName = Type::keyName($object) ?: 'primaryKey';
            $params['primaryKey'] = $key;
            $params[$keyName] = $key;
        }

        return $this->http->post("UpdateObject/update{$object}", $attributes, $params);
    }
}

