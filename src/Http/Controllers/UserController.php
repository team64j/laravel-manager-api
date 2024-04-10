<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use EvolutionCMS\Models\ActiveUserSession;
use EvolutionCMS\Models\User;
use EvolutionCMS\Models\UserAttribute;
use EvolutionCMS\Models\UserRole;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use OpenApi\Annotations as OA;
use Team64j\LaravelManagerApi\Http\Requests\UserRequest;
use Team64j\LaravelManagerApi\Http\Resources\UserResource;
use Team64j\LaravelManagerApi\Layouts\UserLayout;
use Team64j\LaravelManagerApi\Traits\PaginationTrait;

class UserController extends Controller
{
    use PaginationTrait;

    /**
     * @OA\Get(
     *     path="/users",
     *     summary="Получение списка пользователей с пагинацией и фильтрацией",
     *     tags={"Users"},
     *     security={{"Api":{}}},
     *     parameters={
     *         @OA\Parameter (name="filter", in="query", @OA\Schema(type="string")),
     *         @OA\Parameter (name="order", in="query", @OA\Schema(type="string", default="id")),
     *         @OA\Parameter (name="dir", in="query", @OA\Schema(type="string", default="asc")),
     *         @OA\Parameter (name="role", in="query", @OA\Schema(type="string")),
     *         @OA\Parameter (name="blocked", in="query", @OA\Schema(type="string")),
     *     },
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param UserRequest $request
     * @param UserLayout $layout
     *
     * @return AnonymousResourceCollection
     */
    public function index(UserRequest $request, UserLayout $layout): AnonymousResourceCollection
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

        $result = $a->query()
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
                fn($query) => $query->where($u->qualifyColumn('username'), $filterUsername)
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
            ->paginate(Config::get('global.number_of_results'))
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
                'data' => $distinct->keyBy('name')
                    ->sortBy('name')
                    ->map(fn(UserAttribute $item) => [
                        'key' => $item->role,
                        'value' => $item->name,
                        'selected' => $item->role == $filterRole,
                    ])
                    ->prepend([
                        'key' => '',
                        'value' => Lang::get('global.mgrlog_anyall'),
                    ], '')
                    ->values(),
            ],
            [
                'name' => 'lastlogin',
                'type' => 'date',
                'data' => [
                    'from' => date('Y-m-d', $filterDatetime->first() ?: $datetimeMin),
                    'to' => date('Y-m-d', $filterDatetime->last() ?: $datetimeMax),
                    'min' => date('Y-m-d', $datetimeMin),
                    'max' => date('Y-m-d', $datetimeMax),
                ],
            ],
            [
                'name' => 'blocked',
                'data' => $distinct->keyBy('blocked')
                    ->map(fn(UserAttribute $item) => [
                        'key' => $item->blocked,
                        'value' => $item->blocked ? Lang::get('global.yes') : Lang::get('global.no'),
                        'selected' => $item->blocked == $filterBlocked,
                    ])
                    ->prepend([
                        'key' => '',
                        'value' => Lang::get('global.mgrlog_anyall'),
                    ], '')
                    ->sortBy('blocked')
                    ->values(),
            ],
        ];

        return UserResource::collection($result->items())
            ->additional([
                'layout' => $layout->list(),
                'meta' => [
                    'title' => Lang::get('global.users'),
                    'icon' => $layout->getIconList(),
                    'pagination' => $this->pagination($result),
                    'sorting' => [
                        'order' => $order,
                        'dir' => $dir,
                    ],
                    'filters' => $filters,
                ] + ($result->isEmpty() ? ['message' => Lang::get('global.no_results')] : []),
            ]);
    }

    /**
     * @OA\Get(
     *     path="/users/{id}",
     *     summary="Чтение пользователя",
     *     tags={"Users"},
     *     security={{"Api":{}}},
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param UserRequest $request
     * @param string $user
     * @param UserLayout $layout
     *
     * @return UserResource
     */
    public function show(UserRequest $request, string $user, UserLayout $layout): UserResource
    {
        /** @var User $user */
        $user = User::query()->with('attributes')->findOrNew($user);

        return UserResource::make($user)
            ->additional([
                'layout' => $layout->default($user),
                'meta' => [
                    'title' => $user->username ?? Lang::get('global.new_user'),
                    'icon' => $layout->getIcon(),
                ],
            ]);
    }

    /**
     * @OA\Get(
     *     path="/users/list",
     *     summary="Получение списка пользователей с пагинацией для меню",
     *     tags={"Users"},
     *     security={{"Api":{}}},
     *     parameters={
     *         @OA\Parameter (name="filter", in="query", @OA\Schema(type="string")),
     *     },
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param UserRequest $request
     *
     * @return AnonymousResourceCollection
     */
    public function list(UserRequest $request): AnonymousResourceCollection
    {
        $filter = $request->get('filter');

        $result = User::query()
            ->when(
                $filter,
                fn($q) => $q->where('username', 'like', '%' . $filter . '%')->orWhere('username', $filter)
            )
            ->paginate(Config::get('global.number_of_results'), ['id', 'username as name']);

        return UserResource::collection([
            'data' => $result->items(),
            'meta' => [
                'route' => '/users/:id',
                'pagination' => $this->pagination($result),
                'prepend' => [
                    [
                        'name' => Lang::get('global.new_user'),
                        'icon' => 'fa fa-plus-circle',
                        'to' => [
                            'path' => '/users/new',
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/users/active",
     *     summary="Получение списка активных пользователей с пагинацией",
     *     tags={"Users"},
     *     security={{"Api":{}}},
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param UserRequest $request
     *
     * @return AnonymousResourceCollection
     */
    public function active(UserRequest $request): AnonymousResourceCollection
    {
        $result = ActiveUserSession::query()
            ->select(['internalKey', 'internalKey as id', 'ip', 'lasthit'])
            ->with('user', fn($q) => $q->select(['id', 'username']))
            ->orderByDesc('lasthit')
            ->paginate(Config::get('global.number_of_results'));

        return UserResource::collection([
            'data' => $result->items(),
            'meta' => [
                'columns' => [
                    [
                        'key' => 'id',
                        'label' => 'ID',
                        'style' => [
                            'textAlign' => 'right',
                        ],
                    ],
                    [
                        'name' => 'user.username',
                        'label' => Lang::get('global.user'),
                    ],
                    [
                        'name' => 'ip',
                        'label' => 'IP',
                        'style' => [
                            'textAlign' => 'center',
                        ],
                    ],
                    [
                        'name' => 'lasthit',
                        'label' => Lang::get('global.onlineusers_lasthit'),
                        'style' => [
                            'textAlign' => 'center',
                        ],
                    ],
                ],
                'pagination' => $this->pagination($result),
            ],
        ]);
    }
}
