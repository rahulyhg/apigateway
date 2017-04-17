<?php

namespace Zyh\ApiGateway\Provider;

use Zyh\ApiGateway\Routing\Router;
use Zyh\ApiGateway\Routing\UrlGenerator;
use Zyh\ApiGateway\Contract\Routing\Adapter;
use Zyh\ApiGateway\Routing\ResourceRegistrar;
use Zyh\ApiGateway\Contract\Debug\ExceptionHandler;

class RoutingServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->registerRouter();

        $this->registerUrlGenerator();
    }

    /**
     * Register the router.
     */
    protected function registerRouter()
    {
        $this->app->singleton('apigateway.router', function ($app) {
            $router = new Router(
                $app[Adapter::class],
                $app[ExceptionHandler::class],
                $app,
                $this->config('domain'),
                $this->config('prefix')
            );

            $router->setConditionalRequest($this->config('conditionalRequest'));

            return $router;
        });

        $this->app->singleton(ResourceRegistrar::class, function ($app) {
            return new ResourceRegistrar($app[Router::class]);
        });
    }

    /**
     * Register the URL generator.
     */
    protected function registerUrlGenerator()
    {
        $this->app->singleton('apigateway.url', function ($app) {
            $url = new UrlGenerator($app['request']);

            $url->setRouteCollections($app[Router::class]->getRoutes());

            return $url;
        });
    }

    /**
     * Get the URL generator request rebinder.
     *
     * @return \Closure
     */
    private function requestRebinder()
    {
        return function ($app, $request) {
            $app['apigateway.url']->setRequest($request);
        };
    }
}
