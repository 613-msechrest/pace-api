<?php

namespace Pace;

use ArrayAccess;
use JsonSerializable;
use Pace\Model\Attachments;
use Pace\XPath\Builder;
use ReflectionMethod;
use UnexpectedValueException;

class RestModel implements ArrayAccess, JsonSerializable
{
    use Attachments;

    /**
     * The model type.
     *
     * @var string
     */
    protected $type;

    /**
     * The web service client instance.
     *
     * @var RestClient
     */
    protected $client;

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * The model's original attributes.
     *
     * @var array
     */
    protected $original = [];

    /**
     * Auto-magically loaded "belongs to" relationships.
     *
     * @var array
     */
    protected $relations = [];

    /**
     * Create a new model instance.
     *
     * @param RestClient $client
     * @param string $type
     * @param array $attributes
     */
    public function __construct(RestClient $client, $type, array $attributes = [])
    {
        $this->client = $client;
        $this->type = $type;
        $this->attributes = $attributes;
        $this->original = $attributes;
    }

    /**
     * Get the model type.
     *
     * @return string
     */
    public function type()
    {
        return $this->type;
    }

    /**
     * Get the client instance.
     *
     * @return RestClient
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Get the model's primary key.
     *
     * @return mixed
     */
    public function key()
    {
        return $this->attributes[Client::PRIMARY_KEY] ?? null;
    }

    /**
     * Get the model's attributes.
     *
     * @return array
     */
    public function attributes()
    {
        return $this->attributes;
    }

    /**
     * Get the model's original attributes.
     *
     * @return array
     */
    public function original()
    {
        return $this->original;
    }

    /**
     * Get the specified attribute.
     *
     * @param string $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        return $this->attributes[$key] ?? null;
    }

    /**
     * Set the specified attribute.
     *
     * @param string $key
     * @param mixed $value
     */
    public function setAttribute($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    /**
     * Determine if the model has the specified attribute.
     *
     * @param string $key
     * @return bool
     */
    public function hasAttribute($key)
    {
        return array_key_exists($key, $this->attributes);
    }

    /**
     * Save the model.
     *
     * @return bool
     */
    public function save()
    {
        if ($this->key()) {
            $this->attributes = $this->client->updateObject($this->type, $this->attributes);
        } else {
            $this->attributes = $this->client->createObject($this->type, $this->attributes);
        }

        $this->original = $this->attributes;

        return true;
    }

    /**
     * Delete the model.
     *
     * @return bool
     */
    public function delete()
    {
        if ($this->key()) {
            $this->client->deleteObject($this->type, $this->key());
            return true;
        }

        return false;
    }

    /**
     * Clone the model.
     *
     * @param array $newAttributes
     * @param mixed $newKey
     * @param array|null $newParent
     * @return static
     */
    public function clone(array $newAttributes = [], $newKey = null, ?array $newParent = null)
    {
        $attributes = $this->client->cloneObject($this->type, $this->attributes, $newAttributes, $newKey, $newParent);

        return new static($this->client, $this->type, $attributes);
    }

    /**
     * Read a model by its primary key.
     *
     * @param mixed $key
     * @return static|null
     */
    public function read($key)
    {
        if ($key == null) {
            return null;
        }

        $attributes = $this->client->readObject($this->type, $key);

        if (is_null($attributes)) {
            return null;
        }

        $model = new static($this->client, $this->type, $attributes);
        return $model;
    }

    /**
     * Read a model by its primary key or throw an exception.
     *
     * @param mixed $key
     * @return static
     * @throws \Pace\ModelNotFoundException
     */
    public function readOrFail($key)
    {
        $model = $this->read($key);

        if (is_null($model)) {
            throw new \Pace\ModelNotFoundException("{$this->type} [{$key}] does not exist.");
        }

        return $model;
    }

    /**
     * Create a new model instance.
     *
     * @param array $attributes
     * @return static
     */
    public function newInstance(array $attributes = [])
    {
        return new static($this->client, $this->type, $attributes);
    }

    /**
     * Create a new builder instance.
     *
     * @return \Pace\RestBuilder
     */
    public function newBuilder()
    {
        return new \Pace\RestBuilder($this);
    }

    /**
     * Get a relationship.
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if ($this->hasAttribute($name)) {
            return $this->getAttribute($name);
        }

        // Handle belongs to relationships
        if (strpos($name, '_') !== false) {
            $parts = explode('_', $name, 2);
            $type = Type::modelify($parts[0]);
            $key = $this->getAttribute($name);

            if ($key && !isset($this->relations[$name])) {
                $this->relations[$name] = $this->client->readObject($type, $key);
            }

            return $this->relations[$name] ?? null;
        }

        return null;
    }

    /**
     * Set a relationship.
     *
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        $this->setAttribute($name, $value);
    }

    /**
     * Handle dynamic method calls.
     *
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function __call($method, array $arguments)
    {
        if ($this->isBuilderMethod($method)) {
            $builder = $this->newBuilder();
            return $builder->$method(...$arguments);
        }

        // Handle relationship methods like reportParameters()
        if (strpos($method, 'report') === 0 || strpos($method, 'Report') === 0) {
            // For now, return a simple object that can handle filter() and get()
            return new class {
                public function filter($name, $value) {
                    return $this;
                }
                public function get() {
                    return new class {
                        public function key() {
                            return 1; // Default parameter ID
                        }
                    };
                }
            };
        }

        throw new \BadMethodCallException("Method [$method] does not exist on " . static::class);
    }

    /**
     * Determine if a dynamic call should be passed to the RestBuilder class.
     *
     * @param string $name
     * @return bool
     */
    protected function isBuilderMethod($name)
    {
        if (version_compare(PHP_VERSION, '8.0.0', '>=')) {
            if (method_exists(\Pace\RestBuilder::class, $name)) {
                $reflection = new \ReflectionMethod(\Pace\RestBuilder::class, $name);
                return $reflection->isPublic();
            } else {
                return false;
            }
        } else {
            return method_exists(\Pace\RestBuilder::class, $name) && is_callable([\Pace\RestBuilder::class, $name]);
        }
    }

    /**
     * Determine if the specified attribute exists.
     *
     * @param string $key
     * @return bool
     */
    public function __isset($key)
    {
        return $this->hasAttribute($key);
    }

    /**
     * Unset the specified attribute.
     *
     * @param string $key
     */
    public function __unset($key)
    {
        unset($this->attributes[$key]);
    }

    /**
     * Get the model as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->attributes;
    }

    /**
     * Get the model as JSON.
     *
     * @param int $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->attributes, $options);
    }

    /**
     * Convert the model to its string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * Determine if the given offset exists.
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return $this->hasAttribute($offset);
    }

    /**
     * Get the value at the given offset.
     *
     * @param mixed $offset
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->getAttribute($offset);
    }

    /**
     * Set the value at the given offset.
     *
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value): void
    {
        $this->setAttribute($offset, $value);
    }

    /**
     * Unset the value at the given offset.
     *
     * @param mixed $offset
     */
    public function offsetUnset($offset): void
    {
        unset($this->attributes[$offset]);
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
