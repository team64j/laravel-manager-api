<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use EvolutionCMS\Models\User;
use Illuminate\Support\Facades\Lang;
use Team64j\LaravelManagerApi\Components\ActionsButtons;
use Team64j\LaravelManagerApi\Components\Panel;
use Team64j\LaravelManagerApi\Components\Title;

class UserLayout extends Layout
{
    /**
     * @param User|null $model
     *
     * @return array
     */
    public function default(User $model = null): array
    {
        return [
            ActionsButtons::make()
                ->setCancel(
                    Lang::get('global.cancel'),
                    [
                        'path' => '/users',
                        'close' => true,
                    ]
                )
                ->when(
                    $model->getKey(),
                    fn($actions) => $actions->setDelete()->setCopy()
                )
                ->setSaveAnd(),

            Title::make()
                ->setModel('username')
                ->setTitle(Lang::get('global.new_user'))
                ->setIcon('fa fa-user-circle')
                ->setId($model->getKey()),
        ];
    }

    /**
     * @return array
     */
    public function list(): array
    {
        return [
            ActionsButtons::make()
                ->setNew(
                    Lang::get('global.new_user'),
                    'User',
                    'btn-green'
                ),

            Title::make()
                ->setTitle(Lang::get('global.users'))
                ->setIcon('fa fa-users'),

            Panel::make()
                ->setId('users')
                ->setModel('data')
                ->setRoute('/users/:id')
                ->setHistory(true)
                ->addColumn(
                    'id',
                    Lang::get('global.id'),
                    ['width' => '5rem', 'textAlign' => 'right', 'fontWeight' => 'bold'],
                    true
                )
                ->addColumn('username', Lang::get('global.name'), ['fontWeight' => 500], true)
                ->addColumn('fullname', Lang::get('global.user_full_name'), [], true)
                ->addColumn('email', Lang::get('global.email'), [], true)
                ->addColumn(['role', 'rolename'], Lang::get('global.role'), ['width' => '10rem'], true)
                ->addColumn(
                    'lastlogin',
                    Lang::get('global.user_prevlogin'),
                    ['width' => '12rem', 'textAlign' => 'center'],
                    true
                )
                ->addColumn(
                    'logincount',
                    Lang::get('global.user_logincount'),
                    ['width' => '20rem', 'textAlign' => 'center'],
                    true
                )
                ->addColumn(
                    'blocked',
                    Lang::get('global.user_block'),
                    ['width' => '10rem', 'textAlign' => 'center'],
                    true,
                    [
                        0 => '<span class="text-green-600">' . Lang::get('global.no') . '</span>',
                        1 => '<span class="text-rose-600">' . Lang::get('global.yes') . '</span>',
                    ]
                ),
        ];
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return 'fa fa-user-circle';
    }

    /**
     * @return string
     */
    public function getIconList(): string
    {
        return 'fa fa-users';
    }
}
