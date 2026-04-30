<?php

namespace Pace\Contracts;

/**
 * Common contract for the Pace SOAP + REST clients.
 *
 * This is intentionally small: it covers the shared surface area used by
 * consuming Laravel apps (e.g. $client->model('Job')->read(...)).
 */
interface PaceClient
{
    /**
     * Get a model instance for a Pace type.
     *
     * @param mixed $type
     * @return mixed
     */
    public function model($type);

    /**
     * Determine the version of Pace running on the server.
     *
     * @return mixed
     */
    public function version();
}

