<?php

namespace Pace\RestServices;

use Pace\RestService;

class FindObjects extends RestService
{
    /**
     * Find objects.
     *
     * @param string $object
     * @param string $filter
     * @param string|null $txnId
     * @return array
     */
    public function find($object, $filter, $txnId = null)
    {
        $params = [
            'type' => $object,
            'xpath' => $filter,
        ];

        if ($txnId !== null) {
            $params['txnId'] = $txnId;
        }

        // Debug: log the parameters
        error_log("FindObjects params: " . json_encode($params));

        $response = $this->http->get('FindObjects/find', $params);

        return $response;
    }

    /**
     * Find and sort objects.
     *
     * @param string $object
     * @param string $filter
     * @param array $sort
     * @param string|null $txnId
     * @return array
     */
    public function findAndSort($object, $filter, array $sort, $txnId = null)
    {
        $data = [
            'object' => $object,
            'filter' => $filter,
            'sort' => $sort,
        ];

        $params = [];

        if ($txnId !== null) {
            $params['txnId'] = $txnId;
        }

        $response = $this->http->post('FindObjects/findAndSort', $data, $params);

        return $response;
    }
}
