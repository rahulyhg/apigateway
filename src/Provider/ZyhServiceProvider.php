<?php

namespace Zyh\ApiGateway\Provider;

use RuntimeException;
use Zyh\ApiGateway\Auth\Auth;
use Zyh\ApiGateway\Dispatcher;
use Zyh\ApiGateway\Http\Request;
use Zyh\ApiGateway\Http\Response;
use Zyh\ApiGateway\Console\Command;
use Zyh\ApiGateway\Exception\Handler as ExceptionHandler;

class ZyhServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->setResponseStaticInstances();

        Request::setAcceptParser($this->app['Zyh\ApiGateway\Http\Parser\Accept']);

        $this->app->rebinding('apigateway.routes', function ($app, $routes) {
            $app['apigateway.url']->setRouteCollections($routes);
        });
    }

    protected function setResponseStaticInstances()
    {
        Response::setFormatters($this->config('formats'));
        Response::setEventDispatcher($this->app['events']);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerConfig();

        $this->registerClassAliases();

        $this->app->register(RoutingServiceProvider::class);

        $this->app->register(HttpServiceProvider::class);

        $this->registerExceptionHandler();

        $this->registerDispatcher();

        $this->registerAuth();

        $this->registerDocsCommand();

        if (class_exists('Illuminate\Foundation\Application', false)) {
            $this->commands([
                'Zyh\ApiGateway\Console\Command\Cache',
                'Zyh\ApiGateway\Console\Command\Routes',
            ]);
        }
    }

    /**
     * Register the configuration.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->mergeConfigFrom(realpath(__DIR__.'/../../config/apigateway.php'), 'apigateway');

        if (! $this->app->runningInConsole() && empty($this->config('prefix')) && empty($this->config('domain'))) {
            throw new RuntimeException('Unable to boot ApiServiceProvider, configure an API domain or prefix.');
        }
    }

    /**
     * Register the class aliases.
     *
     * @return void
     */
    protected function registerClassAliases()
    {
        $aliases = [
            'Zyh\ApiGateway\Http\Request' => 'Zyh\ApiGateway\Contract\Http\Request',
            'apigateway.dispatcher' => 'Zyh\ApiGateway\Dispatcher',
            'apigateway.http.validator' => 'Zyh\ApiGateway\Http\RequestValidator',
            'apigateway.http.response' => 'Zyh\ApiGateway\Http\Response\Factory',
            'apigateway.router' => 'Zyh\ApiGateway\Routing\Router',
            'apigateway.router.adapter' => 'Zyh\ApiGateway\Contract\Routing\Adapter',
            'apigateway.auth' => 'Zyh\ApiGateway\Auth\Auth',
            'apigateway.limiting' => 'Zyh\ApiGateway\Http\RateLimit\Handler',
            'apigateway.url' => 'Zyh\ApiGateway\Routing\UrlGenerator',
            'apigateway.exception' => ['Zyh\ApiGateway\Exception\Handler', 'Zyh\ApiGateway\Contract\Debug\ExceptionHandler'],
        ];

        foreach ($aliases as $key => $aliases) {
            foreach ((array) $aliases as $alias) {
                $this->app->alias($key, $alias);
            }
        }
    }

    /**
     * Register the exception handler.
     *
     * @return void
     */
    protected function registerExceptionHandler()
    {
        $this->app->singleton('apigateway.exception', function ($app) {
            return new ExceptionHandler($app['Illuminate\Contracts\Debug\ExceptionHandler'], $this->config('errorFormat'), $this->config('debug'));
        });
    }

    /**
     * Register the internal dispatcher.
     *
     * @return void
     */
    public function registerDispatcher()
    {
        $this->app->singleton('apigateway.dispatcher', function ($app) {
            $dispatcher = new Dispatcher($app, $app['files'], $app['Zyh\ApiGateway\Routing\Router'], $app['Zyh\ApiGateway\Auth\Auth']);

            $dispatcher->setSubtype($this->config('subtype'));
            $dispatcher->setStandardsTree($this->config('standardsTree'));
            $dispatcher->setPrefix($this->config('prefix'));
            $dispatcher->setDefaultVersion($this->config('version'));
            $dispatcher->setDefaultDomain($this->config('domain'));
            $dispatcher->setDefaultFormat($this->config('defaultFormat'));

            return $dispatcher;
        });
    }

    /**
     * Register the auth.
     *
     * @return void
     */
    protected function registerAuth()
    {
        $this->app->singleton('apigateway.auth', function ($app) {
            return new Auth($app['Zyh\ApiGateway\Routing\Router'], $app, $this->config('auth'));
        });
    }

    /**
     * Register the documentation command.
     *
     * @return void
     */
    protected function registerDocsCommand()
    {
        $this->app->singleton('Zyh\ApiGateway\Console\Command\Docs', function ($app) {
            return new Command\Docs(
                $app['Zyh\ApiGateway\Routing\Router'],
                $app['Dingo\Blueprint\Blueprint'],
                $app['Dingo\Blueprint\Writer'],
                $this->config('name'),
                $this->config('version')
            );
        });

        $this->commands(['Zyh\ApiGateway\Console\Command\Docs']);
    }
}
