<?php

namespace Pace\RestServices;

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

        $data = empty($body) ? [] : ['object' => $body];

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
