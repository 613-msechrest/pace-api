<?php

namespace Pace\RestServices;

use Pace\RestService;

class CloneObject extends RestService
{
    /**
     * Clone an object.
     *
     * @param string $object
     * @param array $attributes
     * @param array $newAttributes
     * @param int|string|null $newKey
     * @param array|null $newParent
     * @param string|null $txnId
     * @return array
     */
    public function clone($object, array $attributes, array $newAttributes, $newKey = null, array $newParent = null, $txnId = null)
    {
        $data = [
            $object => $attributes,
            $object . 'AttributesToOverride' => $newAttributes,
        ];

        if ($newKey !== null) {
            $data['newPrimaryKey'] = $newKey;
        }

        if ($newParent !== null) {
            $data['newParent'] = $newParent;
        }

        $params = [];

        if ($txnId !== null) {
            $params['txnId'] = $txnId;
        }

        $response = $this->http->post("/CloneObject/clone{$object}", $data, $params);

        return $response;
    }
}
