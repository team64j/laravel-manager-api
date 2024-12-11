<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use OpenApi\Annotations as OA;
use OpenApi\Generator;

/**
 * @OA\Info(
 *     title="OpenAPI Schema",
 *     version="1.1.1",
 * )
 * @OA\SecurityScheme(
 *     securityScheme="Api",
 *     scheme="bearer",
 *     type="http",
 *     in="header"
 * )
 */
class OpenApiController extends Controller
{
    /**
     * @return string
     */
    public function index()
    {
        return cache()->rememberForever(__METHOD__, function () {
            $openapi = Generator::scan(
                [__DIR__],
                [
                    'validate' => true,
                ]
            );

            $openapi->servers = [
                [
                    'url' => route('manager.api'),
                ],
            ];

            return $openapi->toJson(JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);
        });
    }
}
