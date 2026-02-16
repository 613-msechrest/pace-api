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
        // REST API expects path DeleteObject/DeleteObject with query params: type, key, txnId
        $params = [
            'type' => $object,
            'key' => is_array($key) ? implode(':', $key) : (string) $key,
        ];

        if ($txnId !== null) {
            $params['txnId'] = $txnId;
        }

        try {
            return $this->http->delete('DeleteObject/DeleteObject', $params);
        } catch (\Exception $e) {
            // 404 / "Unable to locate object" = already gone; treat as success (idempotent delete)
            if ($e->getCode() === 404 || strpos($e->getMessage(), 'Unable to locate object') !== false) {
                return [];
            }
            throw $e;
        }
    }
}
