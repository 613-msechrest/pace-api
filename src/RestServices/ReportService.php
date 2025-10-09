<?php

namespace Pace\RestServices;

use Pace\RestService;

class ReportService extends RestService
{
    /**
     * Execute the specified report.
     *
     * @param array $wrapper
     * @param string|null $txnId
     * @return array
     */
    public function executeReport(array $wrapper, $txnId = null): array
    {
        $data = ['wrapper' => $wrapper];

        $params = [];

        if ($txnId !== null) {
            $params['txnId'] = $txnId;
        }

        $response = $this->http->post('/ReportService/executeReport', $data, $params);

        return $response;
    }

    /**
     * Print the specified report.
     *
     * @param array $wrapper
     * @param string|null $txnId
     */
    public function printReport(array $wrapper, $txnId = null): void
    {
        $data = ['wrapper' => $wrapper];

        $params = [];

        if ($txnId !== null) {
            $params['txnId'] = $txnId;
        }

        $this->http->post('/ReportService/printReport', $data, $params);
    }
}
