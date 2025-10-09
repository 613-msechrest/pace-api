<?php

namespace Pace\RestServices;

use Pace\RestService;

class SystemInspector extends RestService
{
    /**
     * Get system information.
     *
     * @param string|null $txnId
     * @return array
     */
    public function getSystemInfo($txnId = null)
    {
        $params = [];

        if ($txnId !== null) {
            $params['txnId'] = $txnId;
        }

        $response = $this->http->get('SystemInspector/getSystemInfo', $params);

        return $response;
    }

    /**
     * Get database information.
     *
     * @param string|null $txnId
     * @return array
     */
    public function getDatabaseInfo($txnId = null)
    {
        $params = [];

        if ($txnId !== null) {
            $params['txnId'] = $txnId;
        }

        $response = $this->http->get('SystemInspector/getDatabaseInfo', $params);

        return $response;
    }

    /**
     * Get performance metrics.
     *
     * @param string|null $txnId
     * @return array
     */
    public function getPerformanceMetrics($txnId = null)
    {
        $params = [];

        if ($txnId !== null) {
            $params['txnId'] = $txnId;
        }

        $response = $this->http->get('SystemInspector/getPerformanceMetrics', $params);

        return $response;
    }
}
