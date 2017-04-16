<?php

namespace Zyh\ApiGateway\Contract\Http\RateLimit;

use Zyh\ApiGateway\Http\Request;
use Illuminate\Container\Container;

interface HasRateLimiter
{
    /**
     * 获取一个可用的阀值器
     *
     * @param \Illuminate\Container\Container $app
     * @param \Zyh\ApiGateway\Http\Request         $request
     *
     * @return string
     */
    public function getRateLimiter(Container $app, Request $request);
}
