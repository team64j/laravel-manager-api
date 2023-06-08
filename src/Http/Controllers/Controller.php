<?php

namespace Team64j\LaravelManagerApi\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Team64j\LaravelManagerApi\Contracts\Http\Controller as ControllerContract;

class Controller extends BaseController implements ControllerContract
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;

    /**
     * @var string
     */
    protected string $route = '';

    /**
     * @var array
     */
    protected array $routes = [];

    /**
     * @var array
     */
    protected array $routeOptions = [];

    /**
     * @return string
     */
    public function getRoute(): string
    {
        return $this->route;
    }

    /**
     * @return array
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * @return array
     */
    public function getRouteOptions(): array
    {
        return $this->routeOptions;
    }
}
