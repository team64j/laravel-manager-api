<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use EvolutionCMS\Models\User;
use Illuminate\Support\Facades\Lang;
use Team64j\LaravelManagerComponents\Actions;
use Team64j\LaravelManagerComponents\Panel;
use Team64j\LaravelManagerComponents\Tabs;
use Team64j\LaravelManagerComponents\Title;

class UserLayout extends Layout
{
    /**
     * @return string
     */
    public function icon(): string
    {
        return 'fa fa-user-circle';
    }

    /**
     * @return string
     */
    public function iconList(): string
    {
        return 'fa fa-users';
    }

    /**
     * @param string|null $value
     *
     * @return string
     */
    public function title(string $value = null): string
    {
        return $value ?? Lang::get('global.new_user');
    }

    /**
     * @return string
     */
    public function titleList(): string
    {
        return Lang::get('global.users');
    }

    /**
     * @param User|null $model
     *
     * @return array
     */
    public function default(User $model = null): array
    {
        return [
            Actions::make()
                ->setCancel(
                    Lang::get('global.cancel'),
                    [
                        'path' => '/users',
                        'close' => true,
                    ]
                )
                ->when(
                    $model->getKey(),
                    fn(Actions $actions) => $actions->setDelete()->setCopy()
                )
                ->setSaveAnd(),

            Title::make()
                ->setModel('username')
                ->setTitle($this->title())
                ->setIcon($this->icon())
                ->setId($model->getKey()),
        ];
    }

    /**
     * @return array
     */
    public function list(): array
    {
        return [
            Actions::make()
                ->setNew(
                    $this->title(),
                    '/users/0',
                    'btn-green'
                ),

            Title::make()
                ->setTitle($this->titleList())
                ->setIcon($this->iconList()),

            Tabs::make()
                ->addTab(
                    'users',
                    slot: Panel::make()
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
                ),
        ];
    }
}
