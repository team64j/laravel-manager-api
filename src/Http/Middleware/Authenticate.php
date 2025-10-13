<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    protected function redirectTo(Request $request): ?string
    {
        return '/';
    }
}
