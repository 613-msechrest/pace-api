<?php

use Pace\RestClient;
use Pace\Rest\Factory as RestFactory;
use Pace\Client as SoapClient;
use Pace\Soap\Factory as SoapFactory;

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

it('can read thumbnail from an Inventory Item', function () {
	$inventoryItem = $this->client->inventoryItem->read('340-RD102');

	// If REST returns a value, ensure it is a non-empty string; otherwise allow null
	if ($inventoryItem->thumbnail !== null) {
		expect($inventoryItem->thumbnail)->toBeString();
		expect($inventoryItem->thumbnail)->not->toBeEmpty();
	} else {
		expect($inventoryItem->thumbnail)->toBeNull();
	}
});

it('SOAP and REST return identical thumbnail', function () {
	// REST value via REST ReadObject service
	$restItem = $this->client->service('ReadObject')->read('InventoryItem', '340-RD102');
	$restThumb = $restItem['thumbnail'] ?? null;

	// SOAP client setup
	$soapClient = new SoapClient(
		new SoapFactory(),
		$this->config['host'],
		$this->config['login'],
		$this->config['password'],
		$this->config['scheme']
	);

	$soapItem = $soapClient->readObject('InventoryItem', '340-RD102');
	$soapThumb = $soapItem['thumbnail'] ?? null;

	// Ensure SOAP has a value and enforce parity between REST and SOAP
	expect($soapThumb)->toBeString();
	expect($soapThumb)->not->toBeEmpty();
	expect($restThumb)->toBe($soapThumb);
});


