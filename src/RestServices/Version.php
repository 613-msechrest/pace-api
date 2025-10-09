<?php

namespace Pace\RestServices;

use Pace\RestService;

class Version extends RestService
{
    /**
     * Determine the version of Pace running on the server.
     *
     * @return array
     */
    public function get()
    {
        $response = $this->http->get('Version/getVersion');

        return $response;
    }
}
