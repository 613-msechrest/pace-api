<?php

namespace Pace\RestServices;

use InvalidArgumentException;
use Pace\RestClient;
use Pace\RestService;
use Pace\Type;

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
    public function clone($object, array $attributes, array $newAttributes = [], $newKey = null, array $newParent = null, $txnId = null)
    {
        $params = [
            'keyOfObjectToClone' => $this->resolveKey($object, $attributes),
            'newParentKey' => $this->formatParentKey($newParent),
        ];

        if ($newKey !== null) {
            $params['newPrimaryKey'] = (string) $newKey;
        }

        if ($txnId !== null) {
            $params['txnId'] = $txnId;
        }

        $response = $this->http->post("/CloneObject/clone{$object}", $newAttributes, $params);

        return $response;
    }

    /**
     * Resolve the primary key of the object being cloned.
     *
     * @param string $object
     * @param array $attributes
     * @return string
     */
    protected function resolveKey($object, array $attributes)
    {
        if (!empty($attributes[RestClient::PRIMARY_KEY])) {
            return (string) $attributes[RestClient::PRIMARY_KEY];
        }

        $keyName = Type::keyName($object) ?: Type::camelize($object);

        if (!empty($attributes[$keyName])) {
            return (string) $attributes[$keyName];
        }

        if (!empty($attributes['id'])) {
            return (string) $attributes['id'];
        }

        throw new InvalidArgumentException("Unable to resolve primary key for {$object} clone.");
    }

    /**
     * Format the new parent key for the clone request.
     *
     * @param array|string|int|null $newParent
     * @return string
     */
    protected function formatParentKey($newParent)
    {
        if ($newParent === null) {
            return '';
        }

        if (is_string($newParent) || is_int($newParent)) {
            return (string) $newParent;
        }

        if (is_array($newParent)) {
            return implode(':', array_map('strval', array_values($newParent)));
        }

        return '';
    }
}
