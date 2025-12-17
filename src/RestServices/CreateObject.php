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

        // Wrap attributes in object name (e.g. "customer")
        // This is required by Pace REST services which are thin wrappers around SOAP.
        // The SOAP operation expects a single parameter named after the object type.
        $wrapper = \Pace\Type::camelize($object);
        $payload = [$wrapper => $attributes];

        $response = $this->http->post("CreateObject/create{$object}", $payload, $params);

        return $response;
    }
}
