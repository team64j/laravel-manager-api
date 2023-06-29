<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use Illuminate\Http\JsonResponse;
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
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
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

        return response()
            ->json(
                $openapi,
                200,
                [],
                JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
            );
    }
}
