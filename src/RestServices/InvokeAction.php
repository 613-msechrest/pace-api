<?php

namespace Pace\RestServices;

use Pace\RestModel;
use Pace\RestService;
use Pace\Type;

class InvokeAction extends RestService
{
    /**
     * Invoke action.
     *
     * @param string $action
     * @param array $object
     * @param string|null $txnId
     * @return mixed
     */
    public function invoke($action, $object, $txnId = null)
    {
        $params = [];

        if ($txnId !== null) {
            $params['txnId'] = $txnId;
        }

        [$queryParams, $body] = $this->resolveInvokePayload($action, (array) $object);
        $params = array_merge($params, $queryParams);

        $body = $this->prepareInvokeBody($body);
        $data = $this->formatInvokeBody($action, $body);

        return $this->http->post("InvokeAction/{$action}", $data, $params);
    }

    /**
     * Split invoke parameters into REST query params and request body values.
     *
     * @param string $action
     * @param array $parameters
     * @return array{0: array, 1: array}
     */
    protected function resolveInvokePayload($action, array $parameters)
    {
        $parameters = $this->normalizeInvokeParameters($action, $parameters);
        $queryParamNames = $this->queryParameterNames($action);

        if ($queryParamNames === []) {
            return [[], $parameters];
        }

        $query = [];
        $body = [];

        foreach ($parameters as $key => $value) {
            if (in_array($key, $queryParamNames, true) && $value !== null && $value !== '') {
                $query[$key] = (string) $value;
            } else {
                $body[$key] = $value;
            }
        }

        return [$query, $body];
    }

    /**
     * Normalize common aliases for known invoke actions.
     *
     * @param string $action
     * @param array $parameters
     * @return array
     */
    protected function normalizeInvokeParameters($action, array $parameters)
    {
        if ($action === 'calculateEstimate' && empty($parameters['estimatePrimaryKey'])) {
            $parameters['estimatePrimaryKey'] = Type::resolveKeyValue('Estimate', $parameters);
            unset($parameters['primaryKey'], $parameters['id']);
        }

        return $parameters;
    }

    /**
     * Convert RestModel instances to minimal Pace object references for invoke bodies.
     *
     * @param array $body
     * @return array
     */
    protected function prepareInvokeBody(array $body)
    {
        return $this->prepareInvokeValue($body);
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    protected function prepareInvokeValue($value)
    {
        if ($value instanceof RestModel) {
            return $this->modelToReference($value);
        }

        if (!is_array($value)) {
            return $value;
        }

        $prepared = [];

        foreach ($value as $key => $item) {
            $prepared[$key] = $this->prepareInvokeValue($item);
        }

        return $prepared;
    }

    /**
     * Build the minimal object reference Pace expects in invoke payloads.
     *
     * @param RestModel $model
     * @return array
     */
    protected function modelToReference(RestModel $model)
    {
        $attributes = $model->attributes();
        $refKey = Type::attributeKeyName($model->type(), $attributes);
        $refValue = Type::resolveKeyValue($model->type(), $attributes);

        return [$refKey => $refValue];
    }

    /**
     * Format the POST body for a given invoke action.
     *
     * @param string $action
     * @param array $body
     * @return array
     */
    protected function formatInvokeBody($action, array $body)
    {
        if ($body === []) {
            return [];
        }

        if ($this->usesDirectBody($action)) {
            return $body;
        }

        return ['object' => $body];
    }

    /**
     * Invoke actions whose request body is the schema itself, not wrapped in "object".
     *
     * @param string $action
     * @return bool
     */
    protected function usesDirectBody($action)
    {
        return in_array($action, [
            'convertEstimateToJob',
        ], true);
    }

    /**
     * REST invoke actions that expect simple scalar request parameters.
     *
     * @param string $action
     * @return array<int, string>
     */
    protected function queryParameterNames($action)
    {
        $map = [
            'calculateEstimate' => ['estimatePrimaryKey'],
        ];

        return $map[$action] ?? [];
    }
}
