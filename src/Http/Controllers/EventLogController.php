<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use Illuminate\Support\Collection;
use OpenApi\Attributes as OA;
use Team64j\LaravelManagerApi\Http\Requests\EventLogRequest;
use Team64j\LaravelManagerApi\Http\Resources\JsonResource;
use Team64j\LaravelManagerApi\Http\Resources\JsonResourceCollection;
use Team64j\LaravelManagerApi\Layouts\EventLogLayout;
use Team64j\LaravelManagerApi\Models\EventLog;

class EventLogController extends Controller
{
    public function __construct(protected EventLogLayout $layout) {}

    #[OA\Get(
        path: '/event-log',
        summary: 'Получение списка лога событий с фильтрацией',
        security: [['Api' => []]],
        tags: ['System'],
        parameters: [
            new OA\Parameter(name: 'type', in: 'query', schema: new OA\Schema(type: 'string', default: '')),
            new OA\Parameter(name: 'user', in: 'query', schema: new OA\Schema(type: 'string', default: '')),
            new OA\Parameter(name: 'eventid', in: 'query', schema: new OA\Schema(type: 'string', default: '')),
            new OA\Parameter(name: 'createdon', in: 'query', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'ok',
                content: new OA\JsonContent(type: 'object')
            ),
        ]
    )]
    public function index(EventLogRequest $request): JsonResourceCollection
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
            1 => __('global.information'),
            2 => __('global.warning'),
            3 => __('global.error'),
        ];

        $result = EventLog::query()
            ->select(['id', 'type', 'source', 'createdon', 'eventid', 'user'])
            ->with('users', fn($query) => $query->select('id', 'username'))
            ->when($filterType, fn($query) => $query->where('type', $filterType))
            ->when($filterUser != '', fn($query) => $query->where('user', $filterUser))
            ->when($filterEventId, fn($query) => $query->where('eventid', $filterEventId))
            ->when($filterDatetime->count() == 2, fn($query) => $query->whereBetween('createdon', $filterDatetime))
            ->orderByDesc('id')
            ->paginate(config('global.number_of_results'))
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

        $filterType = $distinct
            ->keyBy('type')
            ->sortBy('type')
            ->map(fn(EventLog $item) => [
                'key'      => $item->type,
                'value'    => $logTypes[$item->type] ?? 1,
                'selected' => $item->type == $filterType,
            ])
            ->prepend([
                'key'   => '',
                'value' => __('global.mgrlog_anyall'),
            ])
            ->values();

        $filterUser = $distinct
            ->keyBy('user')
            ->sortBy('user')
            ->map(fn(EventLog $item) => [
                'key'      => $item->user,
                'value'    => $item->users ? $item->users->username : '-',
                'selected' => $item->user == $filterUser,
            ])
            ->prepend([
                'key'   => '',
                'value' => __('global.mgrlog_anyall'),
            ])
            ->values();

        $filterEventId = $distinct
            ->keyBy('eventid')
            ->sortBy('eventid')
            ->map(fn(EventLog $item) => [
                'key'      => $item->eventid,
                'value'    => $item->eventid,
                'selected' => $item->eventid == $filterEventId,
            ])
            ->prepend([
                'key'   => '',
                'value' => __('global.mgrlog_anyall'),
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
                    'to'   => date('Y-m-d', $filterDatetime->last() ?: $datetime->timestamp_to),
                    'min'  => date('Y-m-d', $datetime->timestamp_from),
                    'max'  => date('Y-m-d', $datetime->timestamp_to),
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

        return JsonResource::collection($result)
            ->layout($this->layout->list())
            ->meta([
                'filters' => $filters,
            ]);
    }

    #[OA\Get(
        path: '/event-log/{id}',
        summary: 'Чтение лога события',
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
    public function show(EventLogRequest $request, int $id): JsonResource
    {
        /** @var EventLog $model */
        $model = EventLog::query()
            ->with('users', fn($query) => $query->select('id', 'username'))
            ->find($id);

        return JsonResource::make([])
            ->layout($this->layout->default($model));
    }
}
