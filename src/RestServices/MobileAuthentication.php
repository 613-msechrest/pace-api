<?php

namespace Pace\RestServices;

use Pace\RestService;

class MobileAuthentication extends RestService
{
    /**
     * Authenticate mobile user.
     *
     * @param string $username
     * @param string $password
     * @param string|null $deviceId
     * @return array
     */
    public function authenticate($username, $password, $deviceId = null)
    {
        $data = [
            'username' => $username,
            'password' => $password,
        ];

        if ($deviceId !== null) {
            $data['deviceId'] = $deviceId;
        }

        $response = $this->http->post('mobileAuthentication/authenticate', $data);

        return $response;
    }

    /**
     * Refresh authentication token.
     *
     * @param string $token
     * @return array
     */
    public function refreshToken($token)
    {
        $data = ['token' => $token];

        $response = $this->http->post('mobileAuthentication/refreshToken', $data);

        return $response;
    }

    /**
     * Logout mobile user.
     *
     * @param string $token
     * @return array
     */
    public function logout($token)
    {
        $data = ['token' => $token];

        $response = $this->http->post('mobileAuthentication/logout', $data);

        return $response;
    }
}
