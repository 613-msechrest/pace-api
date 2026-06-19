<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Pace Server Address
    |--------------------------------------------------------------------------
    |
    | The Pace server host name or IP address (port is optional in both cases).
    |
    | Format: epace.printcompany.com:8443 or 10.10.10.10
    |
    */

    'host' => env('PACE_HOST'),

    /*
    |--------------------------------------------------------------------------
    | Pace System User
    |--------------------------------------------------------------------------
    |
    | The provided system user must have "Allow remote API usage" enabled.
    |
    */

    'login' => env('PACE_LOGIN'),

    'password' => env('PACE_PASSWORD'),

    /*
    |--------------------------------------------------------------------------
    | URL Scheme
    |--------------------------------------------------------------------------
    |
    | Supported: "https", "http"
    |
    */

    'scheme' => env('PACE_SCHEME', 'https'),

    /*
    |--------------------------------------------------------------------------
    | API Protocol
    |--------------------------------------------------------------------------
    |
    | Choose between SOAP and REST API protocols.
    | Supported: "soap", "rest"
    |
    */

    'protocol' => env('PACE_PROTOCOL', 'soap'),

    /*
    |--------------------------------------------------------------------------
    | Licensee Company ID
    |--------------------------------------------------------------------------
    |
    | The Company primary key for the licensee company configured in Pace
    | administration. Required as newParentKey when cloning top-level Estimates
    | via the REST CloneObject API.
    |
    */

    'licensee_company' => env('PACE_LICENSEE_COMPANY', '001'),

];
