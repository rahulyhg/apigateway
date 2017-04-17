<?php

namespace Zyh\ApiGateway\Facade;

use Illuminate\Support\Facades\Facade;

class Route extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'apigateway.router';
    }
}
