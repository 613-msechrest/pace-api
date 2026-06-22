<?php

use Pace\RestClient;
use Pace\RestModel;

afterEach(function () {
    Mockery::close();
});

it('includes primaryKey in update payload when model only has id', function () {
    $client = Mockery::mock(RestClient::class);
    $client->shouldReceive('updateObject')
        ->once()
        ->with('EstimatePaper', Mockery::on(function (array $attributes) {
            return $attributes['primaryKey'] === 2248386
                && $attributes['id'] === 2248386
                && $attributes['buySizeWidth'] === 25.0;
        }))
        ->andReturn([
            'id' => 2248386,
            'primaryKey' => 2248386,
            'buySizeWidth' => 25.0,
        ]);

    $model = new RestModel($client, 'EstimatePaper', [
        'id' => 2248386,
        'buySizeWidth' => 20.0,
    ]);
    $model->exists = true;
    $model->buySizeWidth = 25.0;

    expect($model->save())->toBeTrue();
});

it('overwrites a null dirty primaryKey before update', function () {
    $client = Mockery::mock(RestClient::class);
    $client->shouldReceive('updateObject')
        ->once()
        ->with('EstimatePaper', Mockery::on(function (array $attributes) {
            return $attributes['primaryKey'] === 2248386
                && $attributes['buySizeWidth'] === 25.0;
        }))
        ->andReturn([
            'id' => 2248386,
            'primaryKey' => 2248386,
            'buySizeWidth' => 25.0,
        ]);

    $model = new RestModel($client, 'EstimatePaper', [
        'id' => 2248386,
        'primaryKey' => null,
        'buySizeWidth' => 20.0,
    ]);
    $model->exists = true;
    $model->buySizeWidth = 25.0;

    expect($model->save())->toBeTrue();
});
