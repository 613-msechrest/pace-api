<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Pace\RestClient;
use Pace\Rest\Factory as RestFactory;

// Create REST client
$client = new RestClient(
    new RestFactory(),
    'your-pace-host.com',
    'username',
    'password',
    'https'
);

try {
    // Example 1: Create a customer
    echo "Creating customer...\n";
    $customer = $client->createObject('Customer', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'phone' => '555-1234'
    ]);
    echo "Customer created with ID: " . $customer['primaryKey'] . "\n";

    // Example 2: Read the customer
    echo "\nReading customer...\n";
    $readCustomer = $client->readObject('Customer', $customer['primaryKey']);
    if ($readCustomer) {
        echo "Customer name: " . $readCustomer['name'] . "\n";
    }

    // Example 3: Update the customer
    echo "\nUpdating customer...\n";
    $updatedCustomer = $client->updateObject('Customer', [
        'primaryKey' => $customer['primaryKey'],
        'name' => 'John Smith',
        'email' => 'johnsmith@example.com'
    ]);
    echo "Customer updated\n";

    // Example 4: Find customers
    echo "\nFinding customers...\n";
    $customers = $client->findObjects('Customer', "name like 'John%'");
    echo "Found " . count($customers) . " customers\n";

    // Example 5: Transaction example
    echo "\nTransaction example...\n";
    $txnId = $client->startTransaction();
    try {
        $order = $client->createObject('Order', [
            'customer' => $customer['primaryKey'],
            'orderDate' => date('Y-m-d'),
            'status' => 'Pending'
        ]);
        $client->commitTransaction();
        echo "Order created in transaction: " . $order['primaryKey'] . "\n";
    } catch (Exception $e) {
        $client->rollbackTransaction();
        echo "Transaction rolled back: " . $e->getMessage() . "\n";
    }

    // Example 6: Attachment service
    echo "\nAttachment example...\n";
    $attachmentKey = $client->attachment()->add(
        'Customer',
        $customer['primaryKey'],
        'documents',
        'test.txt',
        'This is test content',
        'General',
        'Test attachment'
    );
    echo "Attachment created with key: " . $attachmentKey . "\n";

    // Example 7: Mobile services (if available)
    echo "\nMobile services example...\n";
    try {
        $auth = $client->mobileAuthentication();
        $token = $auth->authenticate('mobile_user', 'password');
        echo "Mobile authentication successful\n";

        $mobile = $client->mobileGeneral();
        $config = $mobile->getConfiguration();
        echo "Mobile configuration retrieved\n";

        $todos = $client->mobileTodoItems();
        $items = $todos->getTodoItems('mobile_user');
        echo "Todo items retrieved: " . count($items) . " items\n";
    } catch (Exception $e) {
        echo "Mobile services not available: " . $e->getMessage() . "\n";
    }

    // Example 8: System inspection
    echo "\nSystem inspection...\n";
    try {
        $inspector = $client->systemInspector();
        $systemInfo = $inspector->getSystemInfo();
        echo "System info retrieved\n";
        
        $dbInfo = $inspector->getDatabaseInfo();
        echo "Database info retrieved\n";
    } catch (Exception $e) {
        echo "System inspection not available: " . $e->getMessage() . "\n";
    }

    // Example 9: Geolocation
    echo "\nGeolocation example...\n";
    try {
        $geo = $client->geoLocate();
        $location = $geo->locate('123 Main St, Anytown, USA');
        echo "Geolocation retrieved\n";
    } catch (Exception $e) {
        echo "Geolocation not available: " . $e->getMessage() . "\n";
    }

    // Example 10: Version check
    echo "\nVersion check...\n";
    $version = $client->version();
    echo "Pace version: " . $version['version'] . "\n";

    // Cleanup: Delete the customer
    echo "\nCleaning up...\n";
    $client->deleteObject('Customer', $customer['primaryKey']);
    echo "Customer deleted\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Code: " . $e->getCode() . "\n";
}
