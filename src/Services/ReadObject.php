<?php

namespace Pace\Services;

use SoapFault;
use Pace\Client;
use Pace\Service;

class ReadObject extends Service
{
    /**
     * Read an object by its primary key.
     *
     * @param string $object
     * @param int|string $key
     * @return array|null
     * @throws SoapFault if an unexpected SOAP error occurs.
     */
    public function read($object, $key)
    {
        $request = [lcfirst($object) => [Client::PRIMARY_KEY => $key]];

        try {
            $response = $this->soap->{'read' . $object}($request);
            
            $out = $response->out;
            
            // Convert SOAP object to array, preserving all properties including null values
            // Using get_object_vars() instead of (array) cast to preserve null properties
            if (is_object($out)) {
                $result = get_object_vars($out);
                
                // Also use reflection to get any properties that might not be accessible
                // This ensures we capture all fields, even if they're null or protected
                try {
                    $reflection = new \ReflectionObject($out);
                    foreach ($reflection->getProperties() as $property) {
                        $property->setAccessible(true);
                        $propName = $property->getName();
                        $propValue = $property->getValue($out);
                        // Only add if not already set (get_object_vars might have missed it)
                        if (!array_key_exists($propName, $result)) {
                            $result[$propName] = $propValue;
                        }
                    }
                } catch (\ReflectionException $e) {
                    // If reflection fails, continue with get_object_vars result
                }
            } else {
                $result = (array)$out;
            }
            
            // Debug: Log raw SOAP response to see all fields returned
            if (getenv('PACE_API_DEBUG')) {
                error_log("[SOAP ReadObject] Response type: " . gettype($out));
                if (is_object($out)) {
                    error_log("[SOAP ReadObject] Object class: " . get_class($out));
                    error_log("[SOAP ReadObject] get_object_vars: " . json_encode(get_object_vars($out)));
                }
                error_log("[SOAP ReadObject] Final result for {$object} key {$key}: " . json_encode($result));
            }
            
            return $result;

        } catch (SoapFault $exception) {
            if ($this->isObjectNotFound($exception)) {
                return null;
            }

            throw $exception;
        }
    }

    /**
     * Determine if the SOAP fault is for a non-existent object.
     *
     * @param SoapFault $exception
     * @return bool
     */
    protected function isObjectNotFound(SoapFault $exception)
    {
        return strpos($exception->getMessage(), 'Unable to locate object') === 0;
    }
}
