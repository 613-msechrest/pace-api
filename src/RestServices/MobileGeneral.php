<?php

namespace Pace\RestServices;

use Pace\RestService;

class MobileGeneral extends RestService
{
    /**
     * Get mobile app configuration.
     *
     * @param string|null $txnId
     * @return array
     */
    public function getConfiguration($txnId = null)
    {
        $params = [];

        if ($txnId !== null) {
            $params['txnId'] = $txnId;
        }

        $response = $this->http->get('mobileGeneral/getConfiguration', $params);

        return $response;
    }

    /**
     * Get user preferences.
     *
     * @param string $userId
     * @param string|null $txnId
     * @return array
     */
    public function getUserPreferences($userId, $txnId = null)
    {
        $params = ['userId' => $userId];

        if ($txnId !== null) {
            $params['txnId'] = $txnId;
        }

        $response = $this->http->get('mobileGeneral/getUserPreferences', $params);

        return $response;
    }

    /**
     * Update user preferences.
     *
     * @param string $userId
     * @param array $preferences
     * @param string|null $txnId
     * @return array
     */
    public function updateUserPreferences($userId, array $preferences, $txnId = null)
    {
        $data = [
            'userId' => $userId,
            'preferences' => $preferences,
        ];

        $params = [];

        if ($txnId !== null) {
            $params['txnId'] = $txnId;
        }

        $response = $this->http->put('mobileGeneral/updateUserPreferences', $data, $params);

        return $response;
    }
}
