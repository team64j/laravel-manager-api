<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use OpenApi\Annotations as OA;
use OpenApi\Annotations\OpenApi;
use OpenApi\Generator;

/**
 * @OA\Info(
 *     title="Openapi schema",
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
     * @return OpenApi|null
     */
    public function index(): ?OpenApi
    {
        $openapi = Generator::scan(
            [__DIR__],
            [
                'validate' => true,
            ]
        );

        $openapi->servers = [
            [
                'url' => route('manager.api')
            ],
        ];

        return $openapi;
    }
}
