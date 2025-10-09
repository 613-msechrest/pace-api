<?php

namespace Pace\RestServices;

use Closure;
use Throwable;
use Pace\RestService;

class TransactionService extends RestService
{
    /**
     * Wrap the specified closure in a transaction.
     *
     * @param Closure $callback
     */
    public function transaction(Closure $callback)
    {
        $txnId = $this->start();

        try {
            $callback();
        } catch (Throwable $exception) {
            $this->rollback();
            throw $exception;
        }

        $this->commit();
    }

    /**
     * Start a new transaction.
     *
     * @param int $timeout
     * @return string
     */
    public function start(int $timeout = 60)
    {
        $params = ['timeOutInMinutes' => $timeout];

        $response = $this->http->get('TransactionService/startTransaction', $params, ['Accept' => 'text/plain']);

        return $response['transactionId'] ?? $response;
    }

    /**
     * Rollback the transaction.
     *
     * @param string|null $txnId
     */
    public function rollback($txnId = null)
    {
        $params = [];

        if ($txnId !== null) {
            $params['txnId'] = $txnId;
        }

        $this->http->get('TransactionService/rollback', $params);
    }

    /**
     * Commit the transaction.
     *
     * @param string|null $txnId
     */
    public function commit($txnId = null)
    {
        $params = [];

        if ($txnId !== null) {
            $params['txnId'] = $txnId;
        }

        $this->http->get('TransactionService/commit', $params);
    }
}
