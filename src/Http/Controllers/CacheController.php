<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use OpenApi\Attributes as OA;
use Team64j\LaravelManagerApi\Http\Resources\JsonResource;
use Team64j\LaravelManagerApi\Layouts\CacheLayout;

class CacheController extends Controller
{
    #[OA\Get(
        path: '/cache',
        summary: 'Очистка кэша',
        security: [['Api' => []]],
        tags: ['Cache'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'ok',
                content: new OA\JsonContent(type: 'object')
            )
        ]
    )]
    public function index(CacheLayout $layout)
    {
        return JsonResource::make([])
            ->layout($layout->default());
    }
}
