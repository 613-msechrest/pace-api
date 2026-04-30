<?php

namespace Pace\Facades;

use Illuminate\Support\Facades\Facade;

class Pace extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        // Resolve the correct client (SOAP vs REST) based on config('pace.protocol')
        // as registered by PaceServiceProvider.
        return 'pace.client';
    }
}
