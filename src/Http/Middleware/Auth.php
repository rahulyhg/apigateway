<?php

namespace Zyh\ApiGateway\Http\Middleware;

use Closure;
use Zyh\ApiGateway\Routing\Router;
use Zyh\ApiGateway\Auth\Auth as Authentication;

class Auth
{
    /**
     * Router instance.
     *
     * @var \Zyh\ApiGateway\Routing\Router
     */
    protected $router;

    /**
     * Authenticator instance.
     *
     * @var \Zyh\ApiGateway\Auth\Auth
     */
    protected $auth;

    /**
     * Create a new auth middleware instance.
     *
     * @param \Zyh\ApiGateway\Routing\Router $router
     * @param \Zyh\ApiGateway\Auth\Auth      $auth
     *
     * @return void
     */
    public function __construct(Router $router, Authentication $auth)
    {
        $this->router = $router;
        $this->auth = $auth;
    }

    /**
     * Perform authentication before a request is executed.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $route = $this->router->getCurrentRoute();

        if (! $this->auth->check(false)) {
            $this->auth->authenticate($route->getAuthenticationProviders());
        }

        return $next($request);
    }
}
