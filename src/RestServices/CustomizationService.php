<?php

namespace Pace\RestServices;

use Pace\RestService;

class CustomizationService extends RestService
{
    /**
     * Get customization settings.
     *
     * @param string|null $txnId
     * @return array
     */
    public function getSettings($txnId = null)
    {
        $params = [];

        if ($txnId !== null) {
            $params['txnId'] = $txnId;
        }

        $response = $this->http->get('/CustomizationService/getSettings', $params);

        return $response;
    }

    /**
     * Update customization settings.
     *
     * @param array $settings
     * @param string|null $txnId
     * @return array
     */
    public function updateSettings(array $settings, $txnId = null)
    {
        $data = ['settings' => $settings];

        $params = [];

        if ($txnId !== null) {
            $params['txnId'] = $txnId;
        }

        $response = $this->http->put('/CustomizationService/updateSettings', $data, $params);

        return $response;
    }
}
