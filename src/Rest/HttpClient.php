<?php

namespace Pace\Rest;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

class HttpClient
{
    /**
     * The Guzzle HTTP client instance.
     *
     * @var GuzzleClient
     */
    protected $client;

    /**
     * The middleware.
     *
     * @var array
     */
    protected static $middleware = [];

    /**
     * Create a new HTTP client instance.
     *
     * @param string $baseUrl
     * @param array $options
     */
    public function __construct($baseUrl, array $options = [])
    {
        $this->client = new GuzzleClient(array_merge([
            'base_uri' => $baseUrl,
        ], $options));
    }

    /**
     * Add the specified middleware.
     *
     * @param string $name
     * @param callable $callable
     */
    public static function addMiddleware(string $name, callable $callable)
    {
        static::$middleware[$name] = $callable;
    }

    /**
     * Remove the specified middleware.
     *
     * @param string $name
     */
    public static function removeMiddleware(string $name)
    {
        unset(static::$middleware[$name]);
    }

    /**
     * Get the debug information for the instance.
     *
     * @return array
     */
    public function __debugInfo()
    {
        return [
            'info' => 'GuzzleHttp\Client (redacted for security)',
            'middleware' => array_keys(static::$middleware),
        ];
    }

    /**
     * Set HTTP client options.
     *
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->client = new GuzzleClient(array_merge([
            'base_uri' => $this->client->getConfig('base_uri'),
        ], $options));
    }

    /**
     * Make a GET request.
     *
     * @param string $endpoint
     * @param array $params
     * @return array
     */
    public function get($endpoint, array $params = [], array $headers = [])
    {
        $options = ['query' => $params];
        if (!empty($headers)) {
            $options['headers'] = $headers;
        }
        return $this->request('GET', $endpoint, $options);
    }

    /**
     * Make a POST request.
     *
     * @param string $endpoint
     * @param array $data
     * @param array $params
     * @return array
     */
    public function post($endpoint, array $data = [], array $params = [])
    {
        return $this->request('POST', $endpoint, [
            'query' => $params,
            'json' => $data,
        ]);
    }

    /**
     * Make a PUT request.
     *
     * @param string $endpoint
     * @param array $data
     * @param array $params
     * @return array
     */
    public function put($endpoint, array $data = [], array $params = [])
    {
        return $this->request('PUT', $endpoint, [
            'query' => $params,
            'json' => $data,
        ]);
    }

    /**
     * Make a DELETE request.
     *
     * @param string $endpoint
     * @param array $params
     * @return array
     */
    public function delete($endpoint, array $params = [])
    {
        return $this->request('DELETE', $endpoint, ['query' => $params]);
    }

    /**
     * Make an HTTP request.
     *
     * @param string $method
     * @param string $endpoint
     * @param array $options
     * @return array
     */
    protected function request($method, $endpoint, array $options = [])
    {
        try {
            // Apply middleware to modify request options
            $options = $this->applyMiddleware($options);

            if (getenv('PACE_API_DEBUG')) {
                fwrite(STDERR, "[PaceAPI] Request: {$method} {$endpoint} " . json_encode($options) . "\n");
            }

            $response = $this->client->request($method, $endpoint, $options);

            $body = $response->getBody()->getContents();
            
            if (getenv('PACE_API_DEBUG')) {
                fwrite(STDERR, "[PaceAPI] Response: " . substr($body, 0, 1000) . (strlen($body) > 1000 ? '...' : '') . "\n");
            }
            
            // Try to decode as JSON, fall back to raw string
            
            // Try to decode as JSON, fall back to raw string
            $decoded = json_decode($body, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
            
            // Return raw string if not valid JSON
            return $body;

        } catch (ClientException $e) {
            // Handle 4xx errors
            $this->handleClientError($e);
        } catch (ServerException $e) {
            // Handle 5xx errors
            $this->handleServerError($e);
        } catch (RequestException $e) {
            // Handle other request errors
            $this->handleRequestError($e);
        }
        
        // This should never be reached due to exceptions being thrown above
        return null;
    }

    /**
     * Apply middleware to request options.
     *
     * @param array $options
     * @return array
     */
    protected function applyMiddleware(array $options)
    {
        foreach (static::$middleware as $middleware) {
            $options = $middleware($options);
        }

        return $options;
    }

    /**
     * Handle client errors (4xx).
     *
     * @param ClientException $e
     * @throws \Exception
     */
    protected function handleClientError(ClientException $e)
    {
        $statusCode = $e->getResponse()->getStatusCode();
        $body = $e->getResponse()->getBody()->getContents();

        // Handle 404 as object not found (similar to SOAP behavior)
        if ($statusCode === 404) {
            throw new \Exception('Unable to locate object', 404);
        }

        throw new \Exception("Client error: {$statusCode} - {$body}", $statusCode);
    }

    /**
     * Handle server errors (5xx).
     *
     * @param ServerException $e
     * @throws \Exception
     */
    protected function handleServerError(ServerException $e)
    {
        $statusCode = $e->getResponse()->getStatusCode();
        $body = $e->getResponse()->getBody()->getContents();

        throw new \Exception("Server error: {$statusCode} - {$body}", $statusCode);
    }

    /**
     * Handle request errors.
     *
     * @param RequestException $e
     * @throws \Exception
     */
    protected function handleRequestError(RequestException $e)
    {
        throw new \Exception("Request error: " . $e->getMessage(), 0, $e);
    }
}
