<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use OpenApi\Attributes as OA;
use OpenApi\Generator;

#[OA\Info(
    version: '1.1.1',
    title: 'OpenAPI Schema'
)]
#[OA\SecurityScheme(
    securityScheme: 'Api',
    type: 'http',
    in: 'header',
    scheme: 'bearer'
)]
class OpenApiController extends Controller
{
    public function index(): string
    {
        return cache()->rememberForever(__METHOD__, function () {
            $openapi = (new Generator)->generate([__DIR__]);

            $openapi->servers = [
                [
                    'url' => route('manager.api'),
                ],
            ];

            return $openapi->toJson(JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);
        });
    }
}
