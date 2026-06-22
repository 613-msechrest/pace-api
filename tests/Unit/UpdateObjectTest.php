<?php

use Pace\Rest\HttpClient;
use Pace\RestServices\UpdateObject;

afterEach(function () {
    Mockery::close();
});

it('resolves update key from id when primaryKey is absent', function () {
    $http = Mockery::mock(HttpClient::class);
    $http->shouldReceive('post')
        ->once()
        ->with(
            'UpdateObject/updateEstimatePaper',
            ['id' => 2248386, 'buySizeWidth' => 25.0],
            ['primaryKey' => '2248386']
        )
        ->andReturn(['id' => 2248386]);

    $service = new UpdateObject($http);

    $service->update('EstimatePaper', [
        'id' => 2248386,
        'buySizeWidth' => 25.0,
    ]);
});

it('resolves update key from primaryKey when present', function () {
    $http = Mockery::mock(HttpClient::class);
    $http->shouldReceive('post')
        ->once()
        ->with(
            'UpdateObject/updateEstimatePaper',
            ['primaryKey' => 2248386, 'buySizeWidth' => 25.0],
            ['primaryKey' => '2248386']
        )
        ->andReturn(['primaryKey' => 2248386]);

    $service = new UpdateObject($http);

    $service->update('EstimatePaper', [
        'primaryKey' => 2248386,
        'buySizeWidth' => 25.0,
    ]);
});

it('uses type-specific key name for irregular types', function () {
    $http = Mockery::mock(HttpClient::class);
    $http->shouldReceive('post')
        ->once()
        ->with(
            'UpdateObject/updateFileAttachment',
            ['attachment' => 99, 'name' => 'test.txt'],
            ['primaryKey' => '99', 'attachment' => '99']
        )
        ->andReturn(['attachment' => 99]);

    $service = new UpdateObject($http);

    $service->update('FileAttachment', [
        'attachment' => 99,
        'name' => 'test.txt',
    ]);
});
