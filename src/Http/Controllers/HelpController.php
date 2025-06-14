<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use OpenApi\Annotations as OA;
use Team64j\LaravelManagerApi\Http\Requests\HelpRequest;
use Team64j\LaravelManagerApi\Http\Resources\JsonResource;
use Team64j\LaravelManagerApi\Layouts\HelpLayout;

class HelpController extends Controller
{
    /**
     * @OA\Get(
     *     path="/help",
     *     summary="Получение раздела помощи",
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
     * @param HelpRequest $request
     * @param HelpLayout $layout
     *
     * @return JsonResource
     */
    public function index(HelpRequest $request, HelpLayout $layout): JsonResource
    {
        return JsonResource::make([])
            ->layout($layout->default())
            ->meta([
                'title' => $layout->title(),
                'icon' => $layout->icon(),
            ]);
    }
}
