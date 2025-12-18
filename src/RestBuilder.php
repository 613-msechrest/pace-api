<?php

namespace Pace;

use Closure;
use DateTime;
use InvalidArgumentException;
use Pace\ModelNotFoundException;

class RestBuilder
{
    /**
     * Valid operators.
     *
     * @var array
     */
    protected $operators = ['=', '!=', '<', '>', '<=', '>='];

    /**
     * Valid functions.
     *
     * @var array
     */
    protected $functions = ['contains', 'starts-with'];

    /**
     * The filters.
     *
     * @var array
     */
    protected $filters = [];

    /**
     * The sorts.
     *
     * @var array
     */
    protected $sorts = [];

    /**
     * The maximum number of records to return.
     *
     * @var int|null
     */
    protected $limit = null;

    /**
     * The Pace model instance to perform the find request on.
     *
     * @var RestModel
     */
    protected $model;

    /**
     * Create a new instance.
     *
     * @param RestModel|null $model
     */
    public function __construct(?RestModel $model = null)
    {
        $this->model = $model;
    }

    /**
     * Add a "contains" filter.
     *
     * @param string $xpath
     * @param mixed $value
     * @param string $boolean
     * @return self
     */
    public function contains($xpath, $value = null, $boolean = 'and')
    {
        return $this->filter($xpath, 'contains', $value, $boolean);
    }

    /**
     * Add an "or contains" filter.
     *
     * @param string $xpath
     * @param mixed $value
     * @return self
     */
    public function orContains($xpath, $value = null)
    {
        return $this->filter($xpath, 'contains', $value, 'or');
    }

    /**
     * Add a filter.
     *
     * @param string $xpath
     * @param string $operator
     * @param mixed $value
     * @param string $boolean
     * @return self
     */
    public function filter($xpath, $operator = null, $value = null, $boolean = 'and')
    {
        if ($xpath instanceof Closure) {
            return $this->nestedFilter($xpath, $boolean);
        }

        if ($value === null && !$this->isOperator($operator)) {
            list($value, $operator) = [$operator, '='];
        }

        if (!$this->isOperator($operator) && !$this->isFunction($operator)) {
            throw new InvalidArgumentException("Operator '$operator' is not supported");
        }

        $this->filters[] = compact('xpath', 'operator', 'value', 'boolean');

        return $this;
    }

    /**
     * Add a case-insensitive filter.
     *
     * @param string $xpath
     * @param mixed $value
     * @param string $boolean
     * @return self
     */
    public function filterIgnoreCase($xpath, $value, $boolean = 'and')
    {
        $upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lower = 'abcdefghijklmnopqrstuvwxyz';

        $translatedXpath = "translate({$xpath}, '{$upper}', '{$lower}')";

        return $this->filter($translatedXpath, '=', strtolower($value), $boolean);
    }

    /**
     * Set the "limit" value of the query.
     *
     * @param int $value
     * @return self
     */
    public function limit($value)
    {
        $this->limit = max(0, (int) $value);

        return $this;
    }

    /**
     * Alias for the "limit" method.
     *
     * @param int $value
     * @return self
     */
    public function take($value)
    {
        return $this->limit($value);
    }

    /**
     * Perform the find request.
     *
     * @return \Pace\RestKeyCollection
     */
    public function find()
    {
        $xpath = $this->toXPath();
        
        $client = $this->model->getClient();
        
        // Use findAndSort if sorts are specified, otherwise use regular find
        if (count($this->sorts) > 0) {
            // Pass limit to findAndSort so it can be handled by the API
            $results = $client->findAndSortObjects($this->model->type(), $xpath, $this->sorts, $this->limit);
        } else {
            $results = $client->findObjects($this->model->type(), $xpath);
            
            // Apply limit client-side if no sorts (API doesn't support limit without sort)
            if ($this->limit !== null && count($results) > $this->limit) {
                $results = array_slice($results, 0, $this->limit);
            }
        }

        return new RestKeyCollection($this->model, $results);
    }

    /**
     * Get the first matching model.
     *
     * @return RestModel|null
     */
    public function first()
    {
        return $this->find()->first();
    }

    /**
     * Get the first matching model or throw an exception.
     *
     * @return RestModel
     * @throws ModelNotFoundException
     */
    public function firstOrFail()
    {
        $result = $this->first();

        if (is_null($result)) {
            throw new ModelNotFoundException("No filtered results for model [{$this->model->type()}].");
        }

        return $result;
    }

    /**
     * Get the first matching model or a new instance.
     *
     * @return RestModel
     */
    public function firstOrNew()
    {
        return $this->first() ?: $this->model->newInstance();
    }

    /**
     * A more "Eloquent" alias for find().
     *
     * @return \Pace\RestKeyCollection
     */
    public function get()
    {
        return $this->find();
    }

    /**
     * Add an "in" filter.
     *
     * @param string $xpath
     * @param array $values
     * @param string $boolean
     * @return self
     */
    public function in($xpath, array $values, $boolean = 'and')
    {
        return $this->filter(function ($builder) use ($xpath, $values) {
            foreach ($values as $value) {
                $builder->filter($xpath, '=', $value, 'or');
            }
        }, null, null, $boolean);
    }

    /**
     * Add an "or in" filter.
     *
     * @param string $xpath
     * @param array $values
     * @return self
     */
    public function orIn($xpath, array $values)
    {
        return $this->in($xpath, $values, 'or');
    }

    /**
     * Add a nested filter using a callback.
     *
     * @param Closure $callback
     * @param string $boolean
     * @return self
     */
    public function nestedFilter(Closure $callback, $boolean = 'and')
    {
        $builder = new static;

        $callback($builder);

        $this->filters[] = compact('builder', 'boolean');

        return $this;
    }

    /**
     * Add an "or" filter.
     *
     * @param string $xpath
     * @param string $operator
     * @param mixed $value
     * @return self
     */
    public function orFilter($xpath, $operator = null, $value = null)
    {
        return $this->filter($xpath, $operator, $value, 'or');
    }

    /**
     * Add a "starts-with" filter.
     *
     * @param string $xpath
     * @param mixed $value
     * @param string $boolean
     * @return self
     */
    public function startsWith($xpath, $value = null, $boolean = 'and')
    {
        return $this->filter($xpath, 'starts-with', $value, $boolean);
    }

    /**
     * Add an "or starts-with" filter.
     *
     * @param string $xpath
     * @param mixed $value
     * @return self
     */
    public function orStartsWith($xpath, $value = null)
    {
        return $this->filter($xpath, 'starts-with', $value, 'or');
    }

    /**
     * Add a sort.
     *
     * @param string $xpath
     * @param bool $descending
     * @return self
     */
    public function sort($xpath, $descending = false)
    {
        $this->sorts[] = compact('xpath', 'descending');

        return $this;
    }

    /**
     * Convert filters to XPath expression.
     *
     * @return string
     */
    protected function toXPath()
    {
        $xpath = [];

        foreach ($this->filters as $filter) {
            if (isset($filter['builder'])) {
                // Handle nested filters
                $nestedXPath = $filter['builder']->toXPath();
                $xpath[] = sprintf('%s (%s)', $filter['boolean'], $nestedXPath);
            } elseif ($this->isFunction($filter['operator'])) {
                // Handle function filters
                $xpath[] = sprintf('%s %s(%s, %s)',
                    $filter['boolean'], $filter['operator'], $filter['xpath'], $this->formatValue($filter['value']));
            } else {
                // Handle simple filters
                $xpath[] = sprintf('%s %s %s %s',
                    $filter['boolean'], $filter['xpath'], $filter['operator'], $this->formatValue($filter['value']));
            }
        }

        return $this->stripLeadingBoolean(implode(' ', $xpath));
    }

    /**
     * Convert sorts to REST API options format.
     *
     * @return array
     */
    protected function toOptions()
    {
        $options = [];

        if (count($this->sorts)) {
            $options['sort'] = $this->sorts;
        }

        if ($this->limit !== null) {
            $options['limit'] = $this->limit;
        }

        return $options;
    }

    /**
     * Check if an operator is a valid function.
     *
     * @param string $operator
     * @return bool
     */
    protected function isFunction($operator)
    {
        return in_array($operator, $this->functions, true);
    }

    /**
     * Check if an operator is a valid operator.
     *
     * @param string $operator
     * @return bool
     */
    protected function isOperator($operator)
    {
        return in_array($operator, $this->operators, true);
    }

    /**
     * Format a value for XPath expression.
     *
     * @param mixed $value
     * @return string
     */
    protected function formatValue($value)
    {
        switch (true) {
            case ($value === null):
                return 'null';

            case ($value instanceof DateTime):
                // Use date() function syntax as documented in Pace API
                return sprintf('date( %d, %d, %d )', $value->format('Y'), $value->format('n'), $value->format('j'));

            case (is_int($value)):
            case (is_float($value)):
                return (string)$value;

            case (is_bool($value)):
                return $value ? '\'true\'' : '\'false\'';

            default:
                // Choose quote style based on content to avoid escaping
                if (strpos($value, '"') !== false && strpos($value, "'") === false) {
                    // Contains double quotes but no single quotes - use single quotes
                    return "'$value'";
                } elseif (strpos($value, "'") !== false && strpos($value, '"') === false) {
                    // Contains single quotes but no double quotes - use double quotes (no escaping needed)
                    return "\"$value\"";
                } elseif (strpos($value, '"') !== false && strpos($value, "'") !== false) {
                    // Contains both - use single quotes and escape single quotes by doubling
                    $escaped = str_replace("'", "''", $value);
                    return "'$escaped'";
                } else {
                    // No quotes - use double quotes (standard)
                    return "\"$value\"";
                }
        }
    }

    /**
     * Strip the leading boolean from the expression.
     *
     * @param string $xpath
     * @return string
     */
    protected function stripLeadingBoolean($xpath)
    {
        return preg_replace('/^and |^or /', '', $xpath);
    }
}
