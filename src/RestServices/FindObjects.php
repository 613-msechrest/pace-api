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

        // Debug: log the parameters (only if PACE_API_DEBUG is enabled)
        if ($this->shouldLogDebug()) {
            error_log("FindObjects params: " . json_encode($params));
        }

        $response = $this->http->get('FindObjects/find', $params);

        return $response;
    }

    /**
     * Find and sort objects.
     *
     * @param string $object
     * @param string $filter
     * @param array $sort
     * @param int|null $limit
     * @param int|null $offset
     * @param string|null $txnId
     * @return array
     */
    public function findAndSort($object, $filter, array $sort, $limit = null, $offset = null, $txnId = null)
    {
        // type, xpath, offset, and limit go in query params
        // offset is required by the API, default to 0 if not provided
        $params = [
            'type' => $object,
            'xpath' => $filter,
            'offset' => $offset !== null ? $offset : 0,
        ];

        if ($limit !== null) {
            $params['limit'] = $limit;
        }

        if ($txnId !== null) {
            $params['txnId'] = $txnId;
        }

        // Convert sort format from [['xpath' => '@name', 'descending' => false]] 
        // to lowercase format expected by REST API: [{'xpath': '@name', 'descending': false}]
        // Request body is sent directly as the array, not wrapped in a 'sort' key
        $formattedSort = [];
        foreach ($sort as $sortItem) {
            $formattedSort[] = [
                'xpath' => $sortItem['xpath'],
                'descending' => $sortItem['descending'],
            ];
        }

        // Request body is the sort array directly (not wrapped in an object)
        $response = $this->http->post('FindObjects/findSortAndLimit', $formattedSort, $params);

        return $response;
    }
}
