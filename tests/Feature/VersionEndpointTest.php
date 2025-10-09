<?php

use GuzzleHttp\Client as GuzzleClient;

beforeEach(function () {
    $this->config = require __DIR__ . '/../config.php';
    
    // Skip tests if no credentials are provided
    if (!$this->config['host'] || !$this->config['login'] || !$this->config['password']) {
        $this->markTestSkipped('Pace credentials not configured. Create a .env file with PACE_HOST, PACE_LOGIN, and PACE_PASSWORD.');
    }
});

it('can make a GET request to Version/getVersion endpoint', function () {
    $client = new GuzzleClient([
        'base_uri' => "https://{$this->config['host']}/rpc/rest/services/",
        'auth' => [$this->config['login'], $this->config['password']],
        'headers' => [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ],
        'timeout' => 30,
        'verify' => $this->config['verify_ssl'],
    ]);
    
    $response = $client->get('Version/getVersion');
    
    expect($response->getStatusCode())->toBe(200);
    
    $body = $response->getBody()->getContents();
    expect($body)->not->toBeEmpty();
    
    // Try to decode as JSON
    $data = json_decode($body, true);
    if ($data !== null) {
        expect($data)->toBeArray();
        echo "✅ Version endpoint response: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "✅ Version endpoint response (raw): " . $body . "\n";
    }
});
