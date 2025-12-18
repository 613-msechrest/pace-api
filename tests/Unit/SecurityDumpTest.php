<?php

use Pace\RestClient;
use Pace\Rest\Factory as RestFactory;
use Pace\Soap\Factory as SoapFactory;
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
    expect($output)->toContain('Pace\Rest\Factory (redacted)');
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
    expect($output)->toContain('GuzzleHttp\Client (redacted for security)');
});

it('redacts credentials when a Rest Factory is dumped', function () {
    $password = 'SECRET_PASSWORD_12345';
    $factory = new RestFactory();
    $factory->setOptions([
        'auth' => ['admin', $password]
    ]);
    
    $output = print_r($factory, true);

    expect($output)->not->toContain($password);
    expect($output)->not->toContain('admin');
    expect($output)->toContain('******* (redacted)');
});

it('redacts credentials when a Soap Factory is dumped', function () {
    $password = 'SECRET_PASSWORD_12345';
    $factory = new SoapFactory();
    $factory->setOptions([
        'login' => 'admin',
        'password' => $password
    ]);
    
    $output = print_r($factory, true);

    expect($output)->not->toContain($password);
    expect($output)->not->toContain('admin');
    expect($output)->toContain('******* (redacted)');
});
