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

    it('can clone an object and return the new object', function () {
        $estimate = $this->client->estimate->read('1445642');

        expect($estimate)->not->toBeNull();

        $newEstimate = $estimate->clone();

        expect($newEstimate)->toBeInstanceOf(Pace\RestModel::class);
        expect($newEstimate->exists)->toBeTrue();
        expect((string) $newEstimate->key())->not->toBe('1445642');
        expect($newEstimate->key())->not->toBeNull();
    });

    it('can retrieve an attachments content', function () {
        $attachment = $this->client->fileAttachment->read('7iL42VVQ0riaVcm42qV_Aw==');

        expect($attachment)->not->toBeNull();

        $attachmentContent = $attachment->getContent();

        expect($attachmentContent)->toBeString();
        expect($attachmentContent)->not->toBeEmpty();
    });

    it('can invoke an action to convert an estimate to a job', function () {
        $action = $this->client->invokeAction('convertEstimateToJob', [
            'estimate' => ['id' => '1784352'],
            'jobType' => ['id' => 9],
            'createNewJob' => true,
        ]);

        dd($action);

        expect($action)->toBeInstanceOf(Pace\RestModel::class);
        expect($action->job)->toBe('1445642');
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

    // it('can dump the job and discount', function () {
    //     $job = $this->client->job->read('SYST16596');
    //     $discount = $this->client->JobDiscount;

    //     $discount->description = "Testing discount capabilities on a Job level";
    //     $discount->discountAmount = -10.99;
    //     $discount->externalId = "CouponCode1099";
    //     $discount->job = $job->job;
    //     $discount->jobPart = $job->jobParts()->first()->jobPart;
    //     $discount->invoiceExtraType = 4;
    //     $discount->save();
    // });

    // it('can work with UDO_Colorway User Defined Objects (REST with SOAP fallback)', function () {
    //     // Find UDO_Colorway objects - this works via REST
    //     $colorways = $this->client->model('UDO_Colorway')
    //         ->filter('@threadVendor', 'Vendor 1')
    //         ->filter('@jobPartItem', 5875249)
    //         ->get();

    //     // Safely dump the collection - dump keys and count to avoid segfault
    //     dump([
    //         'type' => get_class($colorways),
    //         'count' => $colorways->count(),
    //         'keys' => $colorways->keys(),
    //     ]);

    //     expect($colorways)->toBeInstanceOf(\Pace\RestKeyCollection::class);
    //     expect($colorways->count())->toBeGreaterThan(0);
        
    //     // Verify we have keys
    //     $keys = $colorways->keys();
    //     expect($keys)->toBeArray();
    //     expect(count($keys))->toBeGreaterThan(0);
        
    //     // Try to read the first object - this will use SOAP fallback
    //     $firstKey = $keys[0];
    //     dump(['attempting_to_read_key' => $firstKey]);
        
    //     // REST will delegate to SOAP for UDO read operations
    //     $colorway = $colorways->first();
        
    //     // Debug: Check raw attributes and try different field name variations
    //     $rawAttrs = $colorway->attributes();
    //     dump([
    //         'raw_attributes' => $rawAttrs,
    //         'all_attribute_keys' => array_keys($rawAttrs),
    //         'has_colorwaySequence' => $colorway->hasAttribute('colorwaySequence'),
    //         'has_colorway_sequence' => $colorway->hasAttribute('colorway_sequence'),
    //         'has_ColorwaySequence' => isset($rawAttrs['ColorwaySequence']),
    //         'colorwaySequence_value' => $colorway->hasAttribute('colorwaySequence') ? $colorway->colorwaySequence : 'NOT SET',
    //         'checking_all_keys_for_sequence' => array_filter(array_keys($rawAttrs), function($key) {
    //             return stripos($key, 'sequence') !== false;
    //         }),
    //     ]);
        
    //     // Dump the model for inspection
    //     $attributes = $colorway->toArray();
    //     dump([
    //         'model_type' => get_class($colorway),
    //         'key' => $colorway->key(),
    //         'exists' => $colorway->exists,
    //         'attribute_keys' => array_keys($attributes),
    //         'attributes' => $attributes,
    //     ]);
        
    //     // Verify the model was loaded correctly
    //     expect($colorway !== null)->toBeTrue();
    //     expect($colorway)->toBeInstanceOf(\Pace\RestModel::class);
    //     expect($colorway->type())->toBe('UDO_Colorway');
    //     expect($colorway->exists)->toBeTrue();
    //     expect($colorway->threadVendor)->toBe('Vendor 1');
        
    //     // Test updating the colorway - this will also use SOAP fallback
    //     $originalColorway = $colorway->colorway;
    //     $colorway->colorway = '100';
        
    //     // Note: colorwaySequence may not be returned by SOAP readUDO_Colorway,
    //     // but we can still set and save it. The Pace SOAP API's readUDO_Colorway
    //     // only returns a subset of fields (id, jobPartItem, colorway, threadVendor).
    //     // This is a limitation of the Pace SOAP API itself.
    //     if ($colorway->hasAttribute('colorwaySequence')) {
    //         $originalSequence = $colorway->colorwaySequence;
    //         $colorway->colorwaySequence = 1.1;
    //     } else {
    //         // Field wasn't returned by read, but we can still try to set it
    //         $colorway->setAttribute('colorwaySequence', 1.1);
    //     }
        
    //     $saved = $colorway->save();
        
    //     expect($saved)->toBeTrue();
        
    //     // Verify the update persisted (using SOAP fallback)
    //     // Note: colorwaySequence may still not appear in the read response
    //     // due to Pace SOAP API limitations
    //     $updatedColorway = $this->client->model('UDO_Colorway')->read($colorway->key());
    //     expect($updatedColorway->colorway)->toBe('100');
        
    //     // Restore original value for cleanup
    //     $updatedColorway->colorway = $originalColorway;
    //     $updatedColorway->save();

    //     expect($updatedColorway->colorway)->toBe($originalColorway);
    // });

    // it('can pull the job products', function () {
    //     $jobProduct = $this->client->model('JobProduct')
    //         ->filter('@job', 'D607044')
    //         ->first();

    //     expect($jobProduct)->not->toBeNull();

    //     $keyBeforeDelete = $jobProduct->key();
    //     expect($keyBeforeDelete)->not->toBeNull('JobProduct must have a key to delete');

    //     try {
    //         $jobProduct->delete();
    //     } catch (\Exception $e) {
    //         // Server may block delete when JobProduct is referenced (e.g. by an invoice)
    //         if (strpos($e->getMessage(), 'Still being referenced') !== false) {
    //             $this->markTestSkipped('JobProduct cannot be deleted while referenced by an invoice: ' . $e->getMessage());
    //         }
    //         throw $e;
    //     }

    //     // Verify delete: re-reading by key should return null
    //     $afterDelete = $this->client->readObject('JobProduct', $keyBeforeDelete);
    //     expect($afterDelete)->toBeNull();
    // });
});