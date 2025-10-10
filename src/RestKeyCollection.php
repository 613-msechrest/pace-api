<?php

namespace Pace;

use ArrayAccess;
use Countable;
use Iterator;
use Pace\ModelNotFoundException;

class RestKeyCollection implements ArrayAccess, Countable, Iterator
{
    /**
     * The model instance.
     *
     * @var RestModel
     */
    protected $model;

    /**
     * The collection of keys.
     *
     * @var array
     */
    protected $keys;

    /**
     * The current position.
     *
     * @var int
     */
    protected $position = 0;

    /**
     * Create a new collection instance.
     *
     * @param RestModel $model
     * @param array $keys
     */
    public function __construct(RestModel $model, array $keys)
    {
        $this->model = $model;
        $this->keys = $keys;
    }

    /**
     * Get the first model from the collection.
     *
     * @return RestModel|null
     */
    public function first()
    {
        if (empty($this->keys)) {
            return null;
        }

        return $this->model->read($this->keys[0]);
    }

    /**
     * Get the last model from the collection.
     *
     * @return RestModel|null
     */
    public function last()
    {
        if (empty($this->keys)) {
            return null;
        }

        return $this->model->read(end($this->keys));
    }

    /**
     * Get a slice of the collection.
     *
     * @param int $offset
     * @param int $length
     * @return RestKeyCollection
     */
    public function slice($offset, $length = null)
    {
        $keys = array_slice($this->keys, $offset, $length);
        return new static($this->model, $keys);
    }

    /**
     * Get the count of items in the collection.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->keys);
    }

    /**
     * Determine if the given offset exists.
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return isset($this->keys[$offset]);
    }

    /**
     * Get the value at the given offset.
     *
     * @param mixed $offset
     * @return RestModel|null
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        if (!isset($this->keys[$offset])) {
            return null;
        }

        return $this->model->read($this->keys[$offset]);
    }

    /**
     * Set the value at the given offset.
     *
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->keys[] = $value;
        } else {
            $this->keys[$offset] = $value;
        }
    }

    /**
     * Unset the value at the given offset.
     *
     * @param mixed $offset
     */
    public function offsetUnset($offset): void
    {
        unset($this->keys[$offset]);
    }

    /**
     * Rewind the iterator.
     */
    public function rewind(): void
    {
        $this->position = 0;
    }

    /**
     * Get the current item.
     *
     * @return RestModel|null
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        return $this->offsetGet($this->position);
    }

    /**
     * Get the current key.
     *
     * @return int
     */
    public function key(): int
    {
        return $this->position;
    }

    /**
     * Move to the next item.
     */
    public function next(): void
    {
        $this->position++;
    }

    /**
     * Check if the current position is valid.
     *
     * @return bool
     */
    public function valid(): bool
    {
        return $this->offsetExists($this->position);
    }

    /**
     * Convert the collection to an array.
     *
     * @return array
     */
    public function toArray()
    {
        $models = [];
        foreach ($this->keys as $key) {
            $model = $this->model->read($key);
            if ($model) {
                $models[] = $model;
            }
        }
        return $models;
    }

    /**
     * Get the keys array.
     *
     * @return array
     */
    public function keys()
    {
        return $this->keys;
    }
}
