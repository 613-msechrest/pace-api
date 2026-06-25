<?php

use Pace\Rest\HttpClient;
use Pace\RestServices\InvokeAction;

afterEach(function () {
    Mockery::close();
});

it('sends calculateEstimate key as a query parameter', function () {
    $http = Mockery::mock(HttpClient::class);
    $http->shouldReceive('post')
        ->once()
        ->with(
            'InvokeAction/calculateEstimate',
            [],
            ['estimatePrimaryKey' => '1445642']
        )
        ->andReturn(['success' => true]);

    $service = new InvokeAction($http);

    $service->invoke('calculateEstimate', [
        'estimatePrimaryKey' => '1445642',
    ]);
});

it('maps estimate id aliases to estimatePrimaryKey for calculateEstimate', function () {
    $http = Mockery::mock(HttpClient::class);
    $http->shouldReceive('post')
        ->once()
        ->with(
            'InvokeAction/calculateEstimate',
            [],
            ['estimatePrimaryKey' => '1445642']
        )
        ->andReturn(['success' => true]);

    $service = new InvokeAction($http);

    $service->invoke('calculateEstimate', [
        'id' => '1445642',
    ]);
});

it('keeps unknown invoke actions in the request body', function () {
    $http = Mockery::mock(HttpClient::class);
    $http->shouldReceive('post')
        ->once()
        ->with(
            'InvokeAction/createEstimate',
            [
                'object' => [
                    'customer' => 'HOUSE',
                    'estimateDescription' => 'Testing',
                ],
            ],
            []
        )
        ->andReturn(['estimateNumber' => 'E123']);

    $service = new InvokeAction($http);

    $service->invoke('createEstimate', [
        'customer' => 'HOUSE',
        'estimateDescription' => 'Testing',
    ]);
});

it('sends convertEstimateToJob with direct body and model references', function () {
    $client = Mockery::mock(Pace\RestClient::class);
    $estimate = new Pace\RestModel($client, 'Estimate', [
        'id' => 1791726,
        'primaryKey' => 1791726,
        'description' => 'SR-2 Color Spot Transfer w/ Varsity',
    ]);
    $jobType = new Pace\RestModel($client, 'JobType', [
        'id' => 9,
        'primaryKey' => 9,
        'description' => 'Combo Job',
    ]);

    $http = Mockery::mock(HttpClient::class);
    $http->shouldReceive('post')
        ->once()
        ->with(
            'InvokeAction/convertEstimateToJob',
            [
                'estimate' => ['id' => 1791726],
                'jobType' => ['id' => 9],
                'createNewJob' => true,
            ],
            []
        )
        ->andReturn(['id' => 'R123456']);

    $service = new InvokeAction($http);

    $service->invoke('convertEstimateToJob', [
        'estimate' => $estimate,
        'jobType' => $jobType,
        'createNewJob' => true,
    ]);
});
