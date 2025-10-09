<?php

use Pace\RestClient;
use Pace\Rest\Factory as RestFactory;

beforeEach(function () {
    $this->config = require __DIR__ . '/../config.php';
    
    // Skip tests if no credentials are provided
    if (!$this->config['host'] || !$this->config['login'] || !$this->config['password']) {
        $this->markTestSkipped('Pace credentials not configured. Create a .env file with PACE_HOST, PACE_LOGIN, and PACE_PASSWORD.');
    }
    
    $this->client = new RestClient(
        new RestFactory(),
        $this->config['host'],
        $this->config['login'],
        $this->config['password'],
        $this->config['scheme']
    );
});

it('can create a REST client', function () {
    expect($this->client)->toBeInstanceOf(RestClient::class);
});

it('can get version information', function () {
    $version = $this->client->version();
    
    expect($version)->toBeString();
    expect($version)->not->toBeEmpty();
});

it('can access attachment service', function () {
    $attachmentService = $this->client->attachment();
    
    expect($attachmentService)->toBeInstanceOf(Pace\RestServices\AttachmentService::class);
});

it('can access create object service', function () {
    $createService = $this->client->service('CreateObject');
    
    expect($createService)->toBeInstanceOf(Pace\RestServices\CreateObject::class);
});

it('can access read object service', function () {
    $readService = $this->client->service('ReadObject');
    
    expect($readService)->toBeInstanceOf(Pace\RestServices\ReadObject::class);
});

it('can access update object service', function () {
    $updateService = $this->client->service('UpdateObject');
    
    expect($updateService)->toBeInstanceOf(Pace\RestServices\UpdateObject::class);
});

it('can access delete object service', function () {
    $deleteService = $this->client->service('DeleteObject');
    
    expect($deleteService)->toBeInstanceOf(Pace\RestServices\DeleteObject::class);
});

it('can access find objects service', function () {
    $findService = $this->client->service('FindObjects');
    
    expect($findService)->toBeInstanceOf(Pace\RestServices\FindObjects::class);
});

it('can access transaction service', function () {
    $transactionService = $this->client->service('TransactionService');
    
    expect($transactionService)->toBeInstanceOf(Pace\RestServices\TransactionService::class);
});

it('can access mobile authentication service', function () {
    $mobileAuthService = $this->client->mobileAuthentication();
    
    expect($mobileAuthService)->toBeInstanceOf(Pace\RestServices\MobileAuthentication::class);
});

it('can access mobile general service', function () {
    $mobileGeneralService = $this->client->mobileGeneral();
    
    expect($mobileGeneralService)->toBeInstanceOf(Pace\RestServices\MobileGeneral::class);
});

it('can access mobile todo items service', function () {
    $mobileTodoService = $this->client->mobileTodoItems();
    
    expect($mobileTodoService)->toBeInstanceOf(Pace\RestServices\MobileTodoItems::class);
});

it('can access system inspector service', function () {
    $systemInspector = $this->client->systemInspector();
    
    expect($systemInspector)->toBeInstanceOf(Pace\RestServices\SystemInspector::class);
});

it('can access geolocation service', function () {
    $geoService = $this->client->geoLocate();
    
    expect($geoService)->toBeInstanceOf(Pace\RestServices\GeoLocate::class);
});

it('can access customization service', function () {
    $customService = $this->client->customization();
    
    expect($customService)->toBeInstanceOf(Pace\RestServices\CustomizationService::class);
});

it('can create a model instance', function () {
    $customer = $this->client->Customer;
    
    expect($customer)->toBeInstanceOf(Pace\RestModel::class);
    expect($customer->type())->toBe('Customer');
});

it('can create a report builder', function () {
    $report = $this->client->report();
    
    expect($report)->toBeInstanceOf(Pace\RestReport\Builder::class);
});

it('throws exception for unknown service', function () {
    expect(fn() => $this->client->service('UnknownService'))
        ->toThrow(InvalidArgumentException::class, 'Service [$service] is not implemented');
});
