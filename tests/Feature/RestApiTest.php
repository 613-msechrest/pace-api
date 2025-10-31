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

describe('Version API', function () {
    it('can get version information', function () {
        $version = $this->client->version();
        
        expect($version)->toBeString();
        expect($version)->not->toBeEmpty();
    });
});

describe('Transaction API', function () {
    it('can start a transaction', function () {
        $txnId = $this->client->startTransaction();
        
        expect($txnId)->toBeString();
        expect($txnId)->not->toBeEmpty();
        
        // Clean up
        $this->client->rollbackTransaction($txnId);
    });
    
    it('can handle transaction lifecycle', function () {
        $txnId = $this->client->startTransaction();
        
        expect($txnId)->toBeString();
        
        // Commit transaction
        $this->client->commitTransaction($txnId);
        
        // Should not throw exception
        expect(true)->toBeTrue();
    });
    
    it('can rollback a transaction', function () {
        $txnId = $this->client->startTransaction();
        
        expect($txnId)->toBeString();
        
        // Rollback transaction
        $this->client->rollbackTransaction($txnId);
        
        // Should not throw exception
        expect(true)->toBeTrue();
    });
});

describe('Object CRUD Operations', function () {
    it('can read a non-existent object and return null', function () {
        $result = $this->client->readObject('Customer', 999999);
        
        expect($result)->toBeNull();
    });
    
    it('can find objects with filter', function () {
        $results = $this->client->findObjects('Customer', "@id > 0");
        
        expect($results)->toBeArray();
    });
});

describe('Object-Based API', function () {
    it('can read objects using object-based syntax', function () {
        $customer = $this->client->customer->read(999999);
        
        expect($customer)->toBeNull();
    });
    
    it('can filter objects using object-based syntax', function () {
        $customers = $this->client->Customer
            ->filter('@id', '>', 0)
            ->limit(5)
            ->get();
        
        expect($customers)->toBeInstanceOf(\Pace\RestKeyCollection::class);
    });
    
    it('can get first filtered object', function () {
        $customer = $this->client->Customer
            ->filter('@id', '>', 0)
            ->first();
        
        // This might be null if no customers exist, which is fine for testing
        expect($customer)->toBeInstanceOf(\Pace\RestModel::class);
    });
    
    it('can use contains filter', function () {
        $customers = $this->client->Customer
            ->contains('@custName', 'Test')
            ->get();
        
        expect($customers)->toBeInstanceOf(\Pace\RestKeyCollection::class);
    });
    
    it('can chain multiple filters', function () {
        $customers = $this->client->Customer
            ->filter('@id', '>', 0)
            ->filter('@customerStatus', '=', 'O')
            ->limit(10)
            ->get();
        
        expect($customers)->toBeInstanceOf(\Pace\RestKeyCollection::class);
    });
    
    it('can access inventoryBin object', function () {
        $inventoryBin = $this->client->inventoryBin->read(999999);
        
        expect($inventoryBin)->toBeNull();
    });
    
    it('can access job object with filtering', function () {
        $jobs = $this->client->job
            ->filter('@job', 'S1234567')
            ->get();
        
        expect($jobs)->toBeInstanceOf(\Pace\RestKeyCollection::class);
    });
    
    it('can filter with date equality', function () {
        $customers = $this->client->Customer
            ->filter('@dateSetup', '=', '2023-05-24')
            ->get();
        
        expect($customers)->toBeInstanceOf(\Pace\RestKeyCollection::class);
    });
    
    it('can filter with Carbon date objects', function () {
        $customers = $this->client->Customer
            ->filter('@dateSetup', '=', \Carbon\Carbon::parse('2023-05-24'))
            ->get();
        
        expect($customers)->toBeInstanceOf(\Pace\RestKeyCollection::class);
    });

    it('can filter GLAccountingPeriod with date fields', function () {
        // Test filtering by startDate and endDate using Carbon objects
        $periods = $this->client->GLAccountingPeriod
            ->filter('@startDate', \Carbon\Carbon::parse('2025-10-01'))
            ->filter('@endDate', \Carbon\Carbon::parse('2025-10-31'))
            ->get();
        
        expect($periods)->toBeInstanceOf(\Pace\RestKeyCollection::class);
        expect($periods->count())->toBeGreaterThan(0);
        
        // Verify the first result has the expected dates
        $firstPeriod = $periods->first();
        expect($firstPeriod->startDate)->toBe('2025-10-01');
        expect($firstPeriod->endDate)->toBe('2025-10-31');
    });
});

describe('Mobile Services', function () {
    it('can access mobile authentication service', function () {
        $auth = $this->client->mobileAuthentication();
        
        expect($auth)->toBeInstanceOf(Pace\RestServices\MobileAuthentication::class);
    });
    
    it('can access mobile general service', function () {
        $mobile = $this->client->mobileGeneral();
        
        expect($mobile)->toBeInstanceOf(Pace\RestServices\MobileGeneral::class);
    });
    
    it('can access mobile todo items service', function () {
        $todos = $this->client->mobileTodoItems();
        
        expect($todos)->toBeInstanceOf(Pace\RestServices\MobileTodoItems::class);
    });
});

describe('System Services', function () {
    it('can access system inspector service', function () {
        $inspector = $this->client->systemInspector();
        
        expect($inspector)->toBeInstanceOf(Pace\RestServices\SystemInspector::class);
    });
    
    it('can access geolocation service', function () {
        $geo = $this->client->geoLocate();
        
        expect($geo)->toBeInstanceOf(Pace\RestServices\GeoLocate::class);
    });
    
    it('can access customization service', function () {
        $custom = $this->client->customization();
        
        expect($custom)->toBeInstanceOf(Pace\RestServices\CustomizationService::class);
    });
});

describe('Error Handling', function () {
    it('handles authentication errors gracefully', function () {
        $badClient = new RestClient(
            new RestFactory(),
            $this->config['host'],
            'invalid_user',
            'invalid_password',
            $this->config['scheme']
        );
        
        expect(fn() => $badClient->version())
            ->toThrow(Exception::class);
    });
    
    it('handles network errors gracefully', function () {
        $badClient = new RestClient(
            new RestFactory(),
            'invalid-host.example.com',
            'user',
            'pass',
            'https'
        );
        
        expect(fn() => $badClient->version())
            ->toThrow(Exception::class);
    });
});

describe('HTTP Client', function () {
    it('can make direct HTTP requests', function () {
        $httpClient = (new RestFactory())->make("https://{$this->config['host']}/rpc/rest/services/");
        $httpClient->setOptions([
            'auth' => [$this->config['login'], $this->config['password']],
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ]
        ]);
        
        $response = $httpClient->get('Version/getVersion');
        
        expect($response)->toBeString();
        expect($response)->not->toBeEmpty();
    });
});