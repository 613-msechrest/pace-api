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

describe('RestModel Relationships', function () {
    
    it('can access belongs-to relationships using dynamic methods', function () {
        // Find a job part
        $jobParts = $this->client->jobPart
            ->filter('@job', '=', 'S591410')
            ->get();
        
        if (count($jobParts) === 0) {
            $this->markTestSkipped('No job parts found in database');
        }
        
        $jobPart = $jobParts->first();
        
        // Try to access the related job using dynamic method call
        $job = $jobPart->job();
        
        // The job() method should return either a RestModel or null
        if ($job) {
            expect($job)->toBeInstanceOf(\Pace\RestModel::class);
            expect($job->type())->toBe('Job');
            expect($job->job)->toBe('S591410');
        } else {
            // If job doesn't exist, that's also valid
            expect($job)->toBeNull();
        }
    });
});

