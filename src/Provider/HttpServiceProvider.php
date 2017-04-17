<?php

namespace Zyh\ApiGateway\Provider;

use Zyh\ApiGateway\Auth\Auth;
use Zyh\ApiGateway\Routing\Router;
use Zyh\ApiGateway\Http\Validation;
use Zyh\ApiGateway\Http\Middleware;
use Zyh\ApiGateway\Http\RequestValidator;
use Zyh\ApiGateway\Http\RateLimit\Handler;
use Zyh\ApiGateway\Http\Validation\Accept;
use Zyh\ApiGateway\Http\Validation\Domain;
use Zyh\ApiGateway\Http\Validation\Prefix;
use Zyh\ApiGateway\Http\Middleware\Request;
use Zyh\ApiGateway\Http\Middleware\RateLimit;
use Zyh\ApiGateway\Contract\Debug\ExceptionHandler;
use Zyh\ApiGateway\Http\Middleware\PrepareController;
use Zyh\ApiGateway\Http\Parser\Accept as AcceptParser;
use Zyh\ApiGateway\Http\Middleware\Auth as AuthMiddleware;
use Zyh\ApiGateway\Http\Response\Factory as ResponseFactory;
use Zyh\ApiGateway\Http\RateLimit\Handler as RateLimitHandler;

class HttpServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerRateLimiting();

        $this->registerHttpValidation();

        $this->registerHttpParsers();

        $this->registerResponseFactory();

        $this->registerMiddleware();
    }

    /**
     * Register the rate limiting.
     *
     * @return void
     */
    protected function registerRateLimiting()
    {
        $this->app->singleton('apigateway.limiting', function ($app) {
            return new RateLimitHandler($app, $app['cache'], $this->config('throttling'));
        });
    }

    /**
     * Register the HTTP validation.
     *
     * @return void
     */
    protected function registerHttpValidation()
    {
        $this->app->singleton('apigateway.http.validator', function ($app) {
            return new RequestValidator($app);
        });

        $this->app->singleton(Domain::class, function ($app) {
            return new Validation\Domain($this->config('domain'));
        });

        $this->app->singleton(Prefix::class, function ($app) {
            return new Validation\Prefix($this->config('prefix'));
        });

        $this->app->singleton(Accept::class, function ($app) {
            return new Validation\Accept(
                $this->app[AcceptParser::class],
                $this->config('strict')
            );
        });
    }

    /**
     * Register the HTTP parsers.
     *
     * @return void
     */
    protected function registerHttpParsers()
    {
        $this->app->singleton(AcceptParser::class, function ($app) {
            return new AcceptParser(
                $this->config('standardsTree'),
                $this->config('subtype'),
                $this->config('version'),
                $this->config('defaultFormat')
            );
        });
    }

    /**
     * Register the response factory.
     *
     * @return void
     */
    protected function registerResponseFactory()
    {
        $this->app->singleton('apigateway.http.response', function ($app) {
            return new ResponseFactory($app[Factory::class]);
        });
    }

    /**
     * Register the middleware.
     *
     * @return void
     */
    protected function registerMiddleware()
    {
        $this->app->singleton(Request::class, function ($app) {
            $middleware = new Middleware\Request(
                $app,
                $app[ExceptionHandler::class],
                $app[Router::class],
                $app[RequestValidator::class],
                $app['events']
            );

            $middleware->setMiddlewares($this->config('middleware', false));

            return $middleware;
        });

        $this->app->singleton(AuthMiddleware::class, function ($app) {
            return new Middleware\Auth($app[Router::class], $app[Auth::class]);
        });

        $this->app->singleton(RateLimit::class, function ($app) {
            return new Middleware\RateLimit($app[Router::class], $app[Handler::class]);
        });

        $this->app->singleton(PrepareController::class, function ($app) {
            return new Middleware\PrepareController($app[Router::class]);
        });
    }
}
