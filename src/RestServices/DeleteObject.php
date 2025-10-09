<?php

namespace Pace\RestServices;

use Pace\RestService;

class DeleteObject extends RestService
{
    /**
     * Delete an object.
     *
     * @param string $object
     * @param int|string $key
     * @param string|null $txnId
     * @return array
     */
    public function delete($object, $key, $txnId = null)
    {
        $params = [
            'primaryKey' => $key,
        ];

        if ($txnId !== null) {
            $params['txnId'] = $txnId;
        }

        $response = $this->http->delete("DeleteObject/delete{$object}", $params);

        return $response;
    }
}
