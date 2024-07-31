<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use Illuminate\Support\Facades\Lang;
use OpenApi\Annotations as OA;
use Team64j\LaravelManagerApi\Http\Requests\HelpRequest;
use Team64j\LaravelManagerApi\Http\Resources\HelpResource;
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
     * @return HelpResource
     */
    public function index(HelpRequest $request, HelpLayout $layout): HelpResource
    {
        return HelpResource::make([])
            ->additional([
                'layout' => $layout->default(),
                'meta' => [
                    'title' => $layout->title(),
                    'icon' => $layout->icon(),
                ],
            ]);
    }
}
