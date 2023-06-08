<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Contracts\Http;

interface Controller
{
    /**
     * @return array
     */
    public function getRoutes(): array;

    /**
     * @return string
     */
    public function getRoute(): string;

    /**
     * @return array
     */
    public function getRouteOptions(): array;
}
