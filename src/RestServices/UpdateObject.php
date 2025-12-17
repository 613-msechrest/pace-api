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

        // Extract primary key for query parameters as a fallback
        $keyName = \Pace\Type::keyName($object) ?: 'primaryKey';
        $key = $attributes[$keyName] ?? $attributes['primaryKey'] ?? null;
        if ($key) {
            $params['primaryKey'] = $key;
            $params[$keyName] = $key;
        }

        // Wrap attributes in object name (e.g. "fileAttachment")
        // This is required by Pace REST services which are thin wrappers around SOAP.
        // The SOAP operation expects a single parameter named after the object type.
        $wrapper = \Pace\Type::camelize($object);
        $payload = [$wrapper => $attributes];

        return $this->http->post("UpdateObject/update{$object}", $payload, $params);
    }
}

