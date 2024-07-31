<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Annotations as OA;
use Team64j\LaravelManagerApi\Http\Requests\ScheduleRequest;
use Team64j\LaravelManagerApi\Http\Resources\ScheduleResource;
use Team64j\LaravelManagerApi\Layouts\ScheduleLayout;

class ScheduleController extends Controller
{
    /**
     * @OA\Get(
     *     path="/schedule",
     *     summary="Получение расписания",
     *     tags={"System"},
     *     security={{"Api":{}}},
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param ScheduleRequest $request
     * @param ScheduleLayout $layout
     *
     * @return AnonymousResourceCollection
     */
    public function index(ScheduleRequest $request, ScheduleLayout $layout): AnonymousResourceCollection
    {
        return ScheduleResource::collection([])
            ->additional([
                'layout' => $layout->default(),
                'meta' => [
                    'title' => $layout->title(),
                    'icon' => $layout->icon(),
                ],
            ]);
    }
}
