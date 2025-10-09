<?php

namespace Pace\RestServices;

use Pace\RestService;

class GeoLocate extends RestService
{
    /**
     * Get geolocation data.
     *
     * @param string $address
     * @param string|null $txnId
     * @return array
     */
    public function locate($address, $txnId = null)
    {
        $params = ['address' => $address];

        if ($txnId !== null) {
            $params['txnId'] = $txnId;
        }

        $response = $this->http->get('/GeoLocate/locate', $params);

        return $response;
    }

    /**
     * Get geolocation data for multiple addresses.
     *
     * @param array $addresses
     * @param string|null $txnId
     * @return array
     */
    public function locateMultiple(array $addresses, $txnId = null)
    {
        $data = ['addresses' => $addresses];

        $params = [];

        if ($txnId !== null) {
            $params['txnId'] = $txnId;
        }

        $response = $this->http->post('/GeoLocate/locateMultiple', $data, $params);

        return $response;
    }
}
