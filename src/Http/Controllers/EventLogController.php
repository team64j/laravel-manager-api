<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use EvolutionCMS\Models\EventLog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use OpenApi\Annotations as OA;
use Team64j\LaravelManagerApi\Http\Requests\EventLogRequest;
use Team64j\LaravelManagerApi\Http\Resources\JsonResource;
use Team64j\LaravelManagerApi\Layouts\EventLogLayout;
use Team64j\LaravelManagerApi\Traits\PaginationTrait;

class EventLogController extends Controller
{
    use PaginationTrait;

    /**
     * @OA\Get(
     *     path="/event-log",
     *     summary="Получение списка лога событий с фильтрацией",
     *     tags={"System"},
     *     security={{"Api":{}}},
     *     parameters={
     *         @OA\Parameter (name="type", in="query", @OA\Schema(type="string", default="")),
     *         @OA\Parameter (name="user", in="query", @OA\Schema(type="string", default="")),
     *         @OA\Parameter (name="eventid", in="query", @OA\Schema(type="string", default="")),
     *         @OA\Parameter (name="createdon", in="query", @OA\Schema(type="string")),
     *     },
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param EventLogRequest $request
     * @param EventLogLayout $layout
     *
     * @return ResourceCollection
     */
    public function index(EventLogRequest $request, EventLogLayout $layout): ResourceCollection
    {
        $filterType = $request->input('type', '');
        $filterUser = $request->input('user', '');
        $filterEventId = $request->input('eventid', '');

        /** @var Collection $filterDatetime */
        $filterDatetime = $request->collect('createdon')->map(function ($item, $index) {
            if ($index) {
                $item .= ' 23:59:59';
            } else {
                $item .= ' 00:00:00';
            }

            return strtotime($item);
        });

        $logTypes = [
            1 => Lang::get('global.information'),
            2 => Lang::get('global.warning'),
            3 => Lang::get('global.error'),
        ];

        $result = EventLog::query()
            ->select(['id', 'type', 'source', 'createdon', 'eventid', 'user'])
            ->with('users', fn($query) => $query->select('id', 'username'))
            ->when($filterType, fn($query) => $query->where('type', $filterType))
            ->when($filterUser != '', fn($query) => $query->where('user', $filterUser))
            ->when($filterEventId, fn($query) => $query->where('eventid', $filterEventId))
            ->when($filterDatetime->count() == 2, fn($query) => $query->whereBetween('createdon', $filterDatetime))
            ->orderByDesc('id')
            ->paginate(Config::get('global.number_of_results'))
            ->appends($request->all());

        $datetime = EventLog::query()
            ->selectRaw('MIN(createdon) AS timestamp_from, MAX(createdon) AS timestamp_to')
            ->first();

        $distinct = EventLog::query()
            ->select('type', 'user', 'eventid')
            ->with('users', fn($query) => $query->select('id', 'username'))
            ->when($filterDatetime->count() == 2, fn($query) => $query->whereBetween('createdon', $filterDatetime))
            ->distinct()
            ->get();

        $filterType = $distinct->keyBy('type')
            ->sortBy('type')
            ->map(fn(EventLog $item) => [
                'key' => $item->type,
                'value' => $logTypes[$item->type] ?? 1,
                'selected' => $item->type == $filterType,
            ])
            ->prepend([
                'key' => '',
                'value' => Lang::get('global.mgrlog_anyall'),
            ])
            ->values();

        $filterUser = $distinct->keyBy('user')
            ->sortBy('user')
            ->map(fn(EventLog $item) => [
                'key' => $item->user,
                'value' => $item->users ? $item->users->username : '-',
                'selected' => $item->user == $filterUser,
            ])
            ->prepend([
                'key' => '',
                'value' => Lang::get('global.mgrlog_anyall'),
            ])
            ->values();

        $filterEventId = $distinct->keyBy('eventid')
            ->sortBy('eventid')
            ->map(fn(EventLog $item) => [
                'key' => $item->eventid,
                'value' => $item->eventid,
                'selected' => $item->eventid == $filterEventId,
            ])
            ->prepend([
                'key' => '',
                'value' => Lang::get('global.mgrlog_anyall'),
            ])
            ->values();

        $filters = [
            [
                'name' => 'type',
                'data' => $filterType,
            ],
            [
                'name' => 'createdon',
                'type' => 'date',
                'data' => [
                    'from' => date('Y-m-d', $filterDatetime->first() ?: $datetime->timestamp_from),
                    'to' => date('Y-m-d', $filterDatetime->last() ?: $datetime->timestamp_to),
                    'min' => date('Y-m-d', $datetime->timestamp_from),
                    'max' => date('Y-m-d', $datetime->timestamp_to),
                ],
            ],
            [
                'name' => 'eventid',
                'data' => $filterEventId,
            ],
            [
                'name' => 'user',
                'data' => $filterUser,
            ],
        ];

        return JsonResource::collection($result->items())
            ->additional([
                'layout' => $layout->list(),
                'meta' => [
                    'title' => $layout->titleList(),
                    'icon' => $layout->icon(),
                    'pagination' => $this->pagination($result),
                    'filters' => $filters,
                ],
            ]);
    }

    /**
     * @OA\Get(
     *     path="/event-log/{id}",
     *     summary="Чтение лога события",
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
     * @param EventLogRequest $request
     * @param string $eventlog
     * @param EventLogLayout $layout
     *
     * @return JsonResource
     */
    public function show(EventLogRequest $request, string $eventlog, EventLogLayout $layout): JsonResource
    {
        /** @var EventLog $data */
        $data = EventLog::query()
            ->with('users', fn($query) => $query->select('id', 'username'))
            ->find($eventlog);

        return JsonResource::make([])
            ->additional([
                'layout' => $layout->default($data),
                'meta' => [
                    'title' => $layout->title(),
                    'icon' => $layout->icon(),
                ],
            ]);
    }
}
