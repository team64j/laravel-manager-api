<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Team64j\LaravelManagerApi\Http\Requests\ScheduleRequest;
use Team64j\LaravelManagerApi\Http\Resources\ScheduleResource;
use Team64j\LaravelManagerApi\Layouts\ScheduleLayout;

class ScheduleController extends Controller
{
    /**
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
                    'tab' => $layout->title(),
                ],
            ]);
    }
}
