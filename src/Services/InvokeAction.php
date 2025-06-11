<?php

namespace Pace\Services;

use Pace\Service;

class InvokeAction extends Service
{
    /**
     * Invoke action.
     *
     * @param string $action
     * @param object $object
     * @return mixed
     */
    public function invokeAction($action, $object)
    {
	    $request = ['in0' => $object];

        $response = $this->soap->{$action}($request);

        return $response->out;
    }
}