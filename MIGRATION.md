# Pace API Migration Guide: SOAP to REST

This guide explains how to migrate from the SOAP-based Pace API to the new REST-based API.

## Overview

The Pace API package now supports both SOAP and REST protocols. You can migrate gradually or switch entirely to REST.

## Configuration Changes

### Environment Variables

Add the following environment variable to choose your protocol:

```env
# Use REST API (new)
PACE_PROTOCOL=rest

# Use SOAP API (legacy, default)
PACE_PROTOCOL=soap
```

### Configuration File

The `config/pace.php` file now includes a `protocol` option:

```php
return [
    'host' => env('PACE_HOST'),
    'login' => env('PACE_LOGIN'),
    'password' => env('PACE_PASSWORD'),
    'scheme' => env('PACE_SCHEME', 'https'),
    'protocol' => env('PACE_PROTOCOL', 'soap'), // New option
];
```

## Usage Changes

### Laravel Service Container

The package now registers multiple clients:

```php
// Get the appropriate client based on protocol configuration
$client = app('pace.client');

// Or get specific clients
$soapClient = app(Pace\Client::class);
$restClient = app(Pace\RestClient::class);
```

### Direct Usage

```php
use Pace\RestClient;
use Pace\Rest\Factory as RestFactory;

// Create REST client directly
$client = new RestClient(
    new RestFactory(),
    'your-pace-host.com',
    'username',
    'password',
    'https'
);
```

## API Differences

### Authentication

-   **SOAP**: Uses SOAP headers for authentication
-   **REST**: Uses HTTP Basic Authentication

### Request/Response Format

-   **SOAP**: XML-based requests and responses
-   **REST**: JSON-based requests and responses

### Error Handling

-   **SOAP**: Throws `SoapFault` exceptions
-   **REST**: Throws generic `Exception` with HTTP status codes

### Base URLs

-   **SOAP**: `https://host/rpc/services/`
-   **REST**: `https://host/rpc/rest/services`

## Service Mapping

| SOAP Service                             | REST Endpoint                             | Method |
| ---------------------------------------- | ----------------------------------------- | ------ |
| `AttachmentService/addAttachment`        | `/AttachmentService/addAttachment`        | POST   |
| `AttachmentService/getAttachment`        | `/AttachmentService/getAttachment`        | GET    |
| `AttachmentService/getAllAttachments`    | `/AttachmentService/getAllAttachments`    | GET    |
| `AttachmentService/getAttachmentFromKey` | `/AttachmentService/getAttachmentFromKey` | GET    |
| `AttachmentService/removeAllAttachments` | `/AttachmentService/removeAllAttachments` | DELETE |
| `CloneObject/clone{Object}`              | `/CloneObject/clone{Object}`              | POST   |
| `CreateObject/create{Object}`            | `/CreateObject/create{Object}`            | POST   |
| `ReadObject/read{Object}`                | `/ReadObject/read{Object}`                | GET    |
| `UpdateObject/update{Object}`            | `/UpdateObject/update{Object}`            | PUT    |
| `DeleteObject/delete{Object}`            | `/DeleteObject/delete{Object}`            | DELETE |
| `FindObjects/find`                       | `/FindObjects/find`                       | GET    |
| `FindObjects/findAndSort`                | `/FindObjects/findAndSort`                | POST   |
| `InvokeAction/{action}`                  | `/InvokeAction/{action}`                  | POST   |
| `InvokeProcess/{process}`                | `/InvokeProcess/{process}`                | POST   |
| `InvokePaceConnect/{process}`            | `/InvokePaceConnect/{process}`            | POST   |
| `ReportService/executeReport`            | `/ReportService/executeReport`            | POST   |
| `ReportService/printReport`              | `/ReportService/printReport`              | POST   |
| `TransactionService/startTransaction`    | `/TransactionService/startTransaction`    | POST   |
| `TransactionService/commitTransaction`   | `/TransactionService/commitTransaction`   | POST   |
| `TransactionService/rollbackTransaction` | `/TransactionService/rollbackTransaction` | POST   |
| `Version/get`                            | `/Version/get`                            | GET    |
| `CustomizationService/getSettings`       | `/CustomizationService/getSettings`       | GET    |
| `CustomizationService/updateSettings`    | `/CustomizationService/updateSettings`    | PUT    |
| `GeoLocate/locate`                       | `/GeoLocate/locate`                       | GET    |
| `GeoLocate/locateMultiple`               | `/GeoLocate/locateMultiple`               | POST   |
| `SystemInspector/getSystemInfo`          | `/SystemInspector/getSystemInfo`          | GET    |
| `SystemInspector/getDatabaseInfo`        | `/SystemInspector/getDatabaseInfo`        | GET    |
| `SystemInspector/getPerformanceMetrics`  | `/SystemInspector/getPerformanceMetrics`  | GET    |
| `mobileAuthentication/authenticate`      | `/mobileAuthentication/authenticate`      | POST   |
| `mobileAuthentication/refreshToken`      | `/mobileAuthentication/refreshToken`      | POST   |
| `mobileAuthentication/logout`            | `/mobileAuthentication/logout`            | POST   |
| `mobileGeneral/getConfiguration`         | `/mobileGeneral/getConfiguration`         | GET    |
| `mobileGeneral/getUserPreferences`       | `/mobileGeneral/getUserPreferences`       | GET    |
| `mobileGeneral/updateUserPreferences`    | `/mobileGeneral/updateUserPreferences`    | PUT    |
| `mobileTodoItems/getTodoItems`           | `/mobileTodoItems/getTodoItems`           | GET    |
| `mobileTodoItems/createTodoItem`         | `/mobileTodoItems/createTodoItem`         | POST   |
| `mobileTodoItems/updateTodoItem`         | `/mobileTodoItems/updateTodoItem`         | PUT    |
| `mobileTodoItems/deleteTodoItem`         | `/mobileTodoItems/deleteTodoItem`         | DELETE |
| `mobileTodoItems/completeTodoItem`       | `/mobileTodoItems/completeTodoItem`       | PUT    |

## Migration Steps

### 1. Update Dependencies

```bash
composer update
```

### 2. Test REST API

Set `PACE_PROTOCOL=rest` in your environment and test your application.

### 3. Update Error Handling

Replace SOAP-specific error handling:

```php
// Old SOAP error handling
try {
    $result = $client->readObject('Customer', 123);
} catch (SoapFault $e) {
    if (strpos($e->getMessage(), 'Unable to locate object') === 0) {
        // Handle not found
    }
    throw $e;
}

// New REST error handling
try {
    $result = $client->readObject('Customer', 123);
} catch (Exception $e) {
    if ($e->getCode() === 404 || strpos($e->getMessage(), 'Unable to locate object') === 0) {
        // Handle not found
    }
    throw $e;
}
```

### 4. Update Transaction Handling

Transaction handling remains the same:

```php
$txnId = $client->startTransaction();
try {
    $client->createObject('Customer', $data);
    $client->commitTransaction();
} catch (Exception $e) {
    $client->rollbackTransaction();
    throw $e;
}
```

### 5. New Services Available

The REST API includes additional services not available in SOAP:

```php
// Mobile services
$auth = $client->mobileAuthentication();
$token = $auth->authenticate('username', 'password');

$mobile = $client->mobileGeneral();
$config = $mobile->getConfiguration();

$todos = $client->mobileTodoItems();
$items = $todos->getTodoItems('user123');

// System inspection
$inspector = $client->systemInspector();
$systemInfo = $inspector->getSystemInfo();
$dbInfo = $inspector->getDatabaseInfo();

// Geolocation
$geo = $client->geoLocate();
$location = $geo->locate('123 Main St, City, State');

// Customization
$custom = $client->customization();
$settings = $custom->getSettings();
```

## Backward Compatibility

The package maintains backward compatibility:

-   Existing SOAP code continues to work
-   Same public API for both protocols
-   Gradual migration is supported

## Troubleshooting

### Common Issues

1. **Authentication Errors**: Ensure credentials are correct for REST API
2. **404 Errors**: Check that REST endpoints are available on your Pace server
3. **Timeout Issues**: REST may have different timeout settings

### Debugging

Enable verbose logging to debug REST requests:

```php
$client = new RestClient(
    new RestFactory(),
    'host',
    'user',
    'pass'
);

// Add middleware for debugging
HttpClient::addMiddleware('debug', function ($options) {
    $options['debug'] = true;
    return $options;
});
```

## Support

For issues with the REST API migration, please check:

1. Pace server REST API documentation
2. Swagger UI at: `https://your-pace-host/static/swaggerUI/`
3. Package documentation and examples

## Performance Considerations

-   REST API may have different performance characteristics
-   Consider connection pooling for high-volume applications
-   Monitor response times and adjust timeout settings as needed
