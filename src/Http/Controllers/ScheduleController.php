<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use OpenApi\Attributes as OA;
use Team64j\LaravelManagerApi\Http\Requests\ScheduleRequest;
use Team64j\LaravelManagerApi\Http\Resources\JsonResource;
use Team64j\LaravelManagerApi\Http\Resources\JsonResourceCollection;
use Team64j\LaravelManagerApi\Layouts\ScheduleLayout;

class ScheduleController extends Controller
{
    public function __construct(protected ScheduleLayout $layout) {}

    #[OA\Get(
        path: '/schedule',
        summary: 'Получение расписания',
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
    public function index(ScheduleRequest $request): JsonResourceCollection
    {
        return JsonResource::collection([])
            ->layout($this->layout->default());
    }
}
