<?php

use Pace\RestClient;
use Pace\RestModel;

afterEach(function () {
    Mockery::close();
});

it('sends id in update payload when model has id', function () {
    $client = Mockery::mock(RestClient::class);
    $client->shouldReceive('updateObject')
        ->once()
        ->with('EstimatePaper', Mockery::on(function (array $attributes) {
            return $attributes['id'] === 2248386
                && !array_key_exists('primaryKey', $attributes)
                && $attributes['buySizeWidth'] === 25.0;
        }))
        ->andReturn([
            'id' => 2248386,
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

it('prefers id over primaryKey when both are present', function () {
    $client = Mockery::mock(RestClient::class);
    $client->shouldReceive('updateObject')
        ->once()
        ->with('EstimatePaper', Mockery::on(function (array $attributes) {
            return $attributes['id'] === 2248386
                && !array_key_exists('primaryKey', $attributes)
                && $attributes['buySizeWidth'] === 25.0;
        }))
        ->andReturn([
            'id' => 2248386,
            'buySizeWidth' => 25.0,
        ]);

    $model = new RestModel($client, 'EstimatePaper', [
        'id' => 2248386,
        'primaryKey' => '2248386',
        'buySizeWidth' => 20.0,
    ]);
    $model->exists = true;
    $model->buySizeWidth = 25.0;

    expect($model->save())->toBeTrue();
});

it('uses primaryKey in update payload when model has no id', function () {
    $client = Mockery::mock(RestClient::class);
    $client->shouldReceive('updateObject')
        ->once()
        ->with('Customer', Mockery::on(function (array $attributes) {
            return $attributes['primaryKey'] === 123
                && $attributes['name'] === 'Updated';
        }))
        ->andReturn([
            'primaryKey' => 123,
            'name' => 'Updated',
        ]);

    $model = new RestModel($client, 'Customer', [
        'primaryKey' => 123,
        'name' => 'Original',
    ]);
    $model->exists = true;
    $model->name = 'Updated';

    expect($model->save())->toBeTrue();
});
