<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use OpenApi\Attributes as OA;
use Team64j\LaravelManagerApi\Http\Requests\HelpRequest;
use Team64j\LaravelManagerApi\Http\Resources\JsonResource;
use Team64j\LaravelManagerApi\Layouts\HelpLayout;

class HelpController extends Controller
{
    public function __construct(protected HelpLayout $layout) {}

    #[OA\Get(
        path: '/help',
        summary: 'Получение раздела помощи',
        security: [['Api' => []]],
        tags: ['System'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'ok',
                content: new OA\JsonContent(type: 'object')
            ),
        ]
    )]
    public function index(HelpRequest $request): JsonResource
    {
        return JsonResource::make([])
            ->layout($this->layout->default());
    }
}
