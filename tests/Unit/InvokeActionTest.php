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
