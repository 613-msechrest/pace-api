<?php

use Pace\RestClient;
use Pace\Rest\Factory as RestFactory;
use Pace\RestModel;

it('redacts credentials when a RestClient is dumped', function () {
    $password = 'SECRET_PASSWORD_12345';
    $client = new RestClient(
        new RestFactory(),
        'localhost',
        'admin',
        $password
    );

    $output = print_r($client, true);

    expect($output)->not->toContain($password);
    expect($output)->not->toContain('admin');
    expect($output)->toContain('services');
    expect($output)->toContain('url');
});

it('redacts credentials when a RestModel is dumped', function () {
    $password = 'SECRET_PASSWORD_12345';
    $client = new RestClient(
        new RestFactory(),
        'localhost',
        'admin',
        $password
    );

    $model = new RestModel($client, 'Job', ['id' => '123']);
    $output = print_r($model, true);

    expect($output)->not->toContain($password);
    expect($output)->not->toContain('admin');
    expect($output)->toContain('Pace\RestClient (redacted)');
    expect($output)->toContain('Job');
});

it('redacts credentials when an HttpClient is dumped', function () {
    $password = 'SECRET_PASSWORD_12345';
    $factory = new RestFactory();
    $factory->setOptions([
        'auth' => ['admin', $password]
    ]);
    
    $http = $factory->make('https://localhost');
    $output = print_r($http, true);

    expect($output)->not->toContain($password);
    expect($output)->not->toContain('admin');
    expect($output)->toContain('GuzzleHttp\Client (redacted)');
});

