<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use Illuminate\Support\Collection;
use OpenApi\Attributes as OA;
use Team64j\LaravelManagerApi\Http\Requests\UserRequest;
use Team64j\LaravelManagerApi\Http\Resources\JsonResource;
use Team64j\LaravelManagerApi\Http\Resources\JsonResourceCollection;
use Team64j\LaravelManagerApi\Layouts\UserLayout;
use Team64j\LaravelManagerApi\Models\ActiveUserSession;
use Team64j\LaravelManagerApi\Models\User;
use Team64j\LaravelManagerApi\Models\UserAttribute;
use Team64j\LaravelManagerApi\Models\UserRole;

class UserController extends Controller
{
    public function __construct(protected UserLayout $layout) {}

    #[OA\Get(
        path: '/users',
        summary: 'Получение списка пользователей с пагинацией и фильтрацией',
        security: [['Api' => []]],
        tags: ['Users'],
        parameters: [
            new OA\Parameter(name: 'filter', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'order', in: 'query', schema: new OA\Schema(type: 'string', default: 'id')),
            new OA\Parameter(name: 'dir', in: 'query', schema: new OA\Schema(type: 'string', default: 'asc')),
            new OA\Parameter(name: 'role', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'blocked', in: 'query', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'ok',
                content: new OA\JsonContent(type: 'object')
            ),
        ]
    )]
    public function index(UserRequest $request): JsonResourceCollection
    {
        $filter = $request->get('filter');
        $order = $request->input('order', 'id');
        $dir = $request->input('dir', 'asc');
        $filterUsername = $request->input('username');
        $filterRole = $request->input('role');
        $filterBlocked = $request->input('blocked', '');

        /** @var Collection $filterDatetime */
        $filterDatetime = $request->collect('lastlogin')->map(function ($item, $index) {
            if ($index) {
                $item .= ' 23:59:59';
            } else {
                $item .= ' 00:00:00';
            }

            return strtotime($item);
        });

        $orderFields =
            ['id', 'username', 'fullname', 'email', 'rolename', 'role', 'lastlogin', 'logincount', 'blocked'];

        if (!in_array($order, $orderFields)) {
            $order = 'id';
        }

        if (!in_array($dir, ['asc', 'desc'])) {
            $dir = 'asc';
        }

        $a = new UserAttribute();
        $u = new User();
        $r = new UserRole();

        $result = $a
            ->query()
            ->select([
                $u->qualifyColumn('id'),
                $u->qualifyColumn('username'),
                $a->qualifyColumn('fullname'),
                $a->qualifyColumn('email'),
                $a->qualifyColumn('lastlogin'),
                $a->qualifyColumn('logincount'),
                $a->qualifyColumn('blocked'),
                $r->qualifyColumn('name') . ' as rolename',
            ])
            ->join($u->getTable(), $u->qualifyColumn('id'), $a->qualifyColumn('internalKey'))
            ->join($r->getTable(), $r->qualifyColumn('id'), $a->qualifyColumn('role'))
            ->when(
                $filterUsername,
                fn($query) => $query
                    ->where($u->qualifyColumn('username'), $filterUsername)
                    ->orWhere($u->qualifyColumn('username'), 'like', '%' . $filterUsername . '%')
            )
            ->when(in_array($order, $orderFields), fn($q) => $q->orderBy($order, $dir))
            ->when(
                $filter,
                fn($q) => $q
                    ->where($u->qualifyColumn('username'), 'like', '%' . $filter . '%')
                    ->orWhere($u->qualifyColumn('username'), $filter)
            )
            ->when($filterRole, fn($query) => $query->where($a->qualifyColumn('role'), $filterRole))
            ->when($filterBlocked, fn($query) => $query->where($a->qualifyColumn('blocked'), $filterBlocked))
            ->when(
                $filterDatetime->count() == 2,
                fn($query) => $query->whereBetween($a->qualifyColumn('lastlogin'), $filterDatetime)
            )
            ->paginate(config('global.number_of_results'))
            ->appends($request->all());

        $datetimeMin = UserAttribute::query()->where('lastlogin', '>', 0)->min('lastlogin');
        $datetimeMax = UserAttribute::query()->where('lastlogin', '>', 0)->max('lastlogin');

        $distinct = UserAttribute::query()
            ->distinct()
            ->select(['name', 'role', 'blocked'])
            ->join((new UserRole())->getTable() . ' as r', 'r.id', 'role')
            ->when($filterDatetime->count() > 1, fn($query) => $query->whereBetween('lastlogin', $filterDatetime))
            ->get();

        $filters = [
            'username',
            [
                'name' => 'role',
                'data' => $distinct
                    ->keyBy('name')
                    ->sortBy('name')
                    ->map(fn(UserAttribute $item) => [
                        'key'      => $item->role,
                        'value'    => $item->name,
                        'selected' => $item->role == $filterRole,
                    ])
                    ->prepend([
                        'key'   => '',
                        'value' => __('global.mgrlog_anyall'),
                    ], '')
                    ->values(),
            ],
            [
                'name' => 'lastlogin',
                'type' => 'date',
                'data' => [
                    'from' => date('Y-m-d', $filterDatetime->first() ?: $datetimeMin),
                    'to'   => date('Y-m-d', $filterDatetime->last() ?: $datetimeMax),
                    'min'  => date('Y-m-d', $datetimeMin),
                    'max'  => date('Y-m-d', $datetimeMax),
                ],
            ],
            [
                'name' => 'blocked',
                'data' => $distinct
                    ->keyBy('blocked')
                    ->map(fn(UserAttribute $item) => [
                        'key'      => $item->blocked,
                        'value'    => $item->blocked ? __('global.yes') : __('global.no'),
                        'selected' => $item->blocked == $filterBlocked,
                    ])
                    ->prepend([
                        'key'   => '',
                        'value' => __('global.mgrlog_anyall'),
                    ], '')
                    ->sortBy('blocked')
                    ->values(),
            ],
        ];

        return JsonResource::collection($result)
            ->layout($this->layout->list())
            ->meta(
                [
                    'sorting' => [$order => $dir],
                    'filters' => $filters,
                ] + ($result->isEmpty() ? ['message' => __('global.no_results')] : [])
            );
    }

    #[OA\Get(
        path: '/users/{id}',
        summary: 'Чтение пользователя',
        security: [['Api' => []]],
        tags: ['Users'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'ok',
                content: new OA\JsonContent(type: 'object')
            ),
        ]
    )]
    public function show(UserRequest $request, int $id): JsonResource
    {
        /** @var User $model */
        $model = User::query()->with('attributes')->findOrNew($id);

        if (!$model->getKey()) {
            $model->setAttribute($model->getKeyName(), 0);
        }

        return JsonResource::make($model)
            ->layout($this->layout->default($model));
    }

    #[OA\Get(
        path: '/users/list',
        summary: 'Получение списка пользователей с пагинацией для меню',
        security: [['Api' => []]],
        tags: ['Users'],
        parameters: [
            new OA\Parameter(name: 'filter', in: 'query', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'ok',
                content: new OA\JsonContent(type: 'object')
            ),
        ]
    )]
    public function list(UserRequest $request): JsonResourceCollection
    {
        $filter = $request->get('filter');

        $result = User::query()
            ->when(
                $filter,
                fn($q) => $q->where('username', 'like', '%' . $filter . '%')->orWhere('username', $filter)
            )
            ->paginate(config('global.number_of_results'), ['id', 'username as name']);

        return JsonResource::collection($result)
            ->meta([
                'route'   => '/users/:id',
                'prepend' => [
                    [
                        'name' => __('global.new_user'),
                        'icon' => 'fa fa-plus-circle',
                        'to'   => [
                            'path' => '/users/0',
                        ],
                    ],
                ],
            ]);
    }

    #[OA\Get(
        path: '/users/active',
        summary: 'Получение списка активных пользователей с пагинацией',
        security: [['Api' => []]],
        tags: ['Users'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'ok',
                content: new OA\JsonContent(type: 'object')
            ),
        ]
    )]
    public function active(UserRequest $request): JsonResourceCollection
    {
        $result = ActiveUserSession::query()
            ->select(['internalKey', 'internalKey as id', 'ip', 'lasthit'])
            ->with('user', fn($q) => $q->select(['id', 'username']))
            ->orderByDesc('lasthit')
            ->paginate(config('global.number_of_results'));

        return JsonResource::collection($result->items())
            ->meta([
                'columns' => [
                    [
                        'key'   => 'id',
                        'label' => 'ID',
                        'style' => [
                            'textAlign' => 'right',
                        ],
                    ],
                    [
                        'name'  => 'user.username',
                        'label' => __('global.user'),
                    ],
                    [
                        'name'  => 'ip',
                        'label' => 'IP',
                        'style' => [
                            'textAlign' => 'center',
                        ],
                    ],
                    [
                        'name'  => 'lasthit',
                        'label' => __('global.onlineusers_lasthit'),
                        'style' => [
                            'textAlign' => 'center',
                        ],
                    ],
                ],
            ]);
    }
}
