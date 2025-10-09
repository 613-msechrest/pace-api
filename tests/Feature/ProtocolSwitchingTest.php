<?php

use Pace\Client as SoapClient;
use Pace\RestClient;
use Pace\Soap\Factory as SoapFactory;
use Pace\Rest\Factory as RestFactory;

beforeEach(function () {
    $this->config = require __DIR__ . '/../config.php';
    
    // Skip tests if no credentials are provided
    if (!$this->config['host'] || !$this->config['login'] || !$this->config['password']) {
        $this->markTestSkipped('Pace credentials not configured. Create a .env file with PACE_HOST, PACE_LOGIN, and PACE_PASSWORD.');
    }
});

describe('SOAP Client', function () {
    it('can create a SOAP client', function () {
        $soapClient = new SoapClient(
            new SoapFactory(),
            $this->config['host'],
            $this->config['login'],
            $this->config['password'],
            $this->config['scheme']
        );
        
        expect($soapClient)->toBeInstanceOf(SoapClient::class);
    });
    
    it('can get version information via SOAP', function () {
        $soapClient = new SoapClient(
            new SoapFactory(),
            $this->config['host'],
            $this->config['login'],
            $this->config['password'],
            $this->config['scheme']
        );
        
        $version = $soapClient->version();
        
        expect($version)->toBeArray();
        expect($version)->not->toBeEmpty();
    });
});

describe('REST Client', function () {
    it('can create a REST client', function () {
        $restClient = new RestClient(
            new RestFactory(),
            $this->config['host'],
            $this->config['login'],
            $this->config['password'],
            $this->config['scheme']
        );
        
        expect($restClient)->toBeInstanceOf(RestClient::class);
    });
    
    it('can get version information via REST', function () {
        $restClient = new RestClient(
            new RestFactory(),
            $this->config['host'],
            $this->config['login'],
            $this->config['password'],
            $this->config['scheme']
        );
        
        $version = $restClient->version();
        
        expect($version)->toBeString();
        expect($version)->not->toBeEmpty();
    });
});

describe('Protocol Comparison', function () {
    it('can compare SOAP and REST responses', function () {
        $soapClient = new SoapClient(
            new SoapFactory(),
            $this->config['host'],
            $this->config['login'],
            $this->config['password'],
            $this->config['scheme']
        );
        
        $restClient = new RestClient(
            new RestFactory(),
            $this->config['host'],
            $this->config['login'],
            $this->config['password'],
            $this->config['scheme']
        );
        
        $soapVersion = $soapClient->version();
        $restVersion = $restClient->version();
        
        // SOAP returns array, REST returns string
        expect($soapVersion)->toBeArray();
        expect($restVersion)->toBeString();
        
        // Both should return version information
        expect($soapVersion)->not->toBeEmpty();
        expect($restVersion)->not->toBeEmpty();
        
        // Both should contain the same version string
        $soapVersionString = $soapVersion['string'] ?? '';
        expect($soapVersionString)->toBe($restVersion);
    });
});

describe('Service Provider Pattern', function () {
    it('can simulate Laravel service container', function () {
        // Mock Laravel container
        $container = new class {
            private $bindings = [];
            private $config;
            
            public function __construct() {
                $this->config = ['pace' => require __DIR__ . '/../config.php'];
            }
            
            public function singleton($abstract, $concrete) {
                $this->bindings[$abstract] = $concrete;
            }
            
            public function make($abstract) {
                if (isset($this->bindings[$abstract])) {
                    return $this->bindings[$abstract]($this);
                }
                throw new Exception("Service [$abstract] not found");
            }
            
            public function config($key) {
                return $this->config[$key] ?? null;
            }
            
            public function setConfig($section, $key, $value) {
                $this->config[$section][$key] = $value;
            }
        };
        
        // Register services
        $container->singleton('Pace\Client', function($app) {
            $config = $app->config('pace');
            return new SoapClient(
                new SoapFactory(),
                $config['host'],
                $config['login'],
                $config['password'],
                $config['scheme']
            );
        });
        
        $container->singleton('Pace\RestClient', function($app) {
            $config = $app->config('pace');
            return new RestClient(
                new RestFactory(),
                $config['host'],
                $config['login'],
                $config['password'],
                $config['scheme']
            );
        });
        
        $container->singleton('pace.client', function($app) {
            $config = $app->config('pace');
            
            if ($config['protocol'] === 'rest') {
                return $app->make('Pace\RestClient');
            }
            
            return $app->make('Pace\Client');
        });
        
        // Test protocol switching
        $container->setConfig('pace', 'protocol', 'rest');
        
        $restClient = $container->make('pace.client');
        expect($restClient)->toBeInstanceOf(RestClient::class);
        
        $container->setConfig('pace', 'protocol', 'soap');
        $soapClient = $container->make('pace.client');
        expect($soapClient)->toBeInstanceOf(SoapClient::class);
    });
});
