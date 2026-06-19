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
        $keyOfObjectToClone = $this->resolveKey($object, $attributes);

        $source = array_merge($attributes, [RestClient::PRIMARY_KEY => $keyOfObjectToClone]);

        $body = [
            $object => $source,
            $object . 'AttributesToOverride' => $newAttributes === [] ? new \stdClass() : $newAttributes,
        ];

        $params = [
            'keyOfObjectToClone' => $keyOfObjectToClone,
            'newParentKey' => $this->resolveNewParentKey($object, $source, $newParent),
        ];

        if ($newKey !== null) {
            $params['newPrimaryKey'] = (string) $newKey;
            $body['newPrimaryKey'] = $newKey;
        }

        if ($newParent !== null) {
            $body['newParent'] = $newParent;
        }

        if ($txnId !== null) {
            $params['txnId'] = $txnId;
        }

        $response = $this->http->post("CloneObject/clone{$object}", $body, $params);

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
     * Resolve the parent key required by the REST clone endpoint.
     *
     * @param string $object
     * @param array $attributes
     * @param array|string|int|null $newParent
     * @return string
     */
    protected function resolveNewParentKey($object, array $attributes, $newParent)
    {
        if ($newParent !== null) {
            return $this->formatParentKey($newParent);
        }

        // Child estimate objects use the parent estimate key.
        if (strpos($object, 'Estimate') === 0 && $object !== 'Estimate') {
            foreach (['estimate', 'estimateNumber', RestClient::PRIMARY_KEY] as $field) {
                if (!empty($attributes[$field])) {
                    return (string) $attributes[$field];
                }
            }
        }

        // Top-level estimates require the licensee company configured in Pace admin.
        if ($object === 'Estimate') {
            return $this->licenseeCompanyId();
        }

        if (!empty($attributes['customer'])) {
            return (string) $attributes['customer'];
        }

        if (!empty($attributes['company'])) {
            return (string) $attributes['company'];
        }

        return '';
    }

    /**
     * The licensee company ID configured in Pace administration.
     *
     * @return string
     */
    protected function licenseeCompanyId()
    {
        if (function_exists('config')) {
            $configured = config('pace.licensee_company');

            if ($configured !== null && $configured !== '') {
                return (string) $configured;
            }
        }

        $env = getenv('PACE_LICENSEE_COMPANY');

        if ($env !== false && $env !== '') {
            return (string) $env;
        }

        return '001';
    }

    /**
     * Format an explicit new parent key for the clone request.
     *
     * @param array|string|int $newParent
     * @return string
     */
    protected function formatParentKey($newParent)
    {
        if (is_string($newParent) || is_int($newParent)) {
            return (string) $newParent;
        }

        if (is_array($newParent)) {
            return implode(':', array_map('strval', array_values($newParent)));
        }

        return '';
    }
}
