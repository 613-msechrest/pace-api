<?php

namespace Pace\RestServices;

use Pace\Client;
use Pace\RestService;

class ReadObject extends RestService
{
    /**
     * Read an object by its primary key.
     *
     * @param string $object
     * @param int|string $key
     * @param string|null $txnId
     * @return array|null
     */
    public function read($object, $key, $txnId = null)
    {
        $params = [
            'primaryKey' => $key,
        ];

        if ($txnId !== null) {
            $params['txnId'] = $txnId;
        }

        try {
            $response = $this->http->post("ReadObject/read{$object}", [], $params);
            return $response;

        } catch (\Exception $exception) {
            if ($this->isObjectNotFound($exception)) {
                return null;
            }

            throw $exception;
        }
    }

    /**
     * Determine if the exception is for a non-existent object.
     *
     * @param \Exception $exception
     * @return bool
     */
    protected function isObjectNotFound(\Exception $exception)
    {
        return strpos($exception->getMessage(), 'Unable to locate object') !== false ||
               $exception->getCode() === 404 ||
               $exception->getCode() === 500;
    }
}
