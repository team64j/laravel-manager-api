<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Team64j\LaravelEvolution\Models\ManagerLog;
use Team64j\LaravelManagerApi\Http\Requests\SystemLogRequest;
use Team64j\LaravelManagerApi\Http\Resources\SystemLogResource;
use Team64j\LaravelManagerApi\Layouts\SystemLogLayout;
use Team64j\LaravelManagerApi\Traits\PaginationTrait;

class SystemLogController extends Controller
{
    use PaginationTrait;

    protected array $routeOptions = [
        'only' => ['index']
    ];

    /**
     * @param SystemLogRequest $request
     * @param SystemLogLayout $layout
     *
     * @return AnonymousResourceCollection
     */
    public function index(SystemLogRequest $request, SystemLogLayout $layout): AnonymousResourceCollection
    {
        $order = $request->input('order', 'id');
        $dir = $request->input('dir', 'desc');
        $filterUsername = $request->input('username');
        $filterAction = $request->input('action');
        $filterItemId = $request->input('itemid', '');
        $filterItemName = $request->input('itemname');

        /** @var Collection $filterDatetime */
        $filterDatetime = $request->collect('timestamp')->map(function ($item, $index) {
            if ($index) {
                $item .= ' 23:59:59';
            } else {
                $item .= ' 00:00:00';
            }

            return strtotime($item);
        });

        $fields = ['id', 'username', 'action', 'message', 'itemid', 'itemname', 'timestamp', 'ip', 'useragent'];

        if (!in_array($order, $fields)) {
            $order = 'id';
        }

        if (!in_array($dir, ['asc', 'desc'])) {
            $dir = 'asc';
        }

        $result = ManagerLog::query()
            ->select($fields)
            ->when($filterDatetime->count() == 2, fn($query) => $query->whereBetween('timestamp', $filterDatetime))
            ->when($filterUsername, fn($query) => $query->where('username', $filterUsername))
            ->when($filterAction, fn($query) => $query->where('action', (int) $filterAction))
            ->when($filterItemId != '', fn($query) => $query->where('itemid', (int) $filterItemId))
            ->when($filterItemName, fn($query) => $query->where('itemname', $filterItemName))
            ->orderBy($order, $dir)
            ->paginate(Config::get('global.number_of_results'))
            ->appends($request->all());

        $datetime = ManagerLog::query()
            ->selectRaw('MIN(timestamp) AS timestamp_from, MAX(timestamp) AS timestamp_to')
            ->first();

        $distinct = ManagerLog::query()
            ->select('username', 'action', 'message', 'itemid', 'itemname')
            ->when($filterDatetime->count(), fn($query) => $query->whereBetween('timestamp', $filterDatetime))
            ->distinct()
            ->get();

        $filterUsername = $distinct->keyBy('username')
            ->sortBy('username')
            ->map(fn(ManagerLog $item) => [
                'key' => $item->username,
                'value' => $item->username,
                'selected' => $item->username == $filterUsername,
            ])
            ->prepend([
                'key' => '',
                'value' => Lang::get('global.mgrlog_anyall'),
            ], '')
            ->filter(fn($item) => $item['value'])
            ->values();

        $filterAction = $distinct->keyBy('action')
            ->sortBy('action')
            ->map(fn(ManagerLog $item) => [
                'key' => $item->action,
                'value' => $item->action . ' - ' . $item->message,
                'selected' => $item->action == $filterAction,
            ])
            ->prepend([
                'key' => '',
                'value' => Lang::get('global.mgrlog_anyall'),
            ], 0)
            ->filter(fn($item) => $item['value'])
            ->values();

        $filterItemId = $distinct->keyBy('itemid')
            ->sortBy('itemid')
            ->map(fn(ManagerLog $item) => [
                'key' => $item->itemid,
                'value' => $item->itemid,
                'selected' => $item->itemid == $filterItemId,
            ])
            ->prepend([
                'key' => '',
                'value' => Lang::get('global.mgrlog_anyall'),
            ], '')
            ->values();

        $filterItemName = $distinct->keyBy('itemname')
            ->sortBy('itemname', SORT_FLAG_CASE | SORT_NATURAL)
            ->map(fn(ManagerLog $item) => [
                'key' => $item->itemname,
                'value' => $item->itemname,
                'selected' => $item->itemname == $filterItemName,
            ])
            ->prepend([
                'key' => '',
                'value' => Lang::get('global.mgrlog_anyall'),
            ], 0)
            ->filter(fn($item) => $item['value'])
            ->values();

        $filters = [
            [
                'name' => 'username',
                'data' => $filterUsername,
            ],
            [
                'name' => 'action',
                'data' => $filterAction,
                'placeholder' => Lang::get('global.mgrlog_action'),
            ],
            [
                'name' => 'itemid',
                'data' => $filterItemId,
            ],
            [
                'name' => 'itemname',
                'data' => $filterItemName,
            ],
            [
                'name' => 'timestamp',
                'type' => 'date',
                'data' => [
                    'from' => date('Y-m-d', $filterDatetime->first() ?: $datetime->timestamp_from),
                    'to' => date('Y-m-d', $filterDatetime->last() ?: $datetime->timestamp_to),
                    'min' => date('Y-m-d', $datetime->timestamp_from),
                    'max' => date('Y-m-d', $datetime->timestamp_to),
                ],
            ],
        ];

        return SystemLogResource::collection([
            'data' => [
                'data' => $result->items(),
                'sorting' => [
                    'order' => $order,
                    'dir' => $dir,
                ],
                'filters' => $filters,
                'pagination' => $this->pagination($result),
            ],
        ])->additional([
            'layout' => $layout->list(),
            'meta' => [
                'tab' => $layout->title(),
            ]
        ]);
    }
}
