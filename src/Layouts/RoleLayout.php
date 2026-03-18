<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Team64j\LaravelManagerApi\Models\UserRole;
use Team64j\LaravelManagerComponents\Actions;
use Team64j\LaravelManagerComponents\Checkbox;
use Team64j\LaravelManagerComponents\GlobalTab;
use Team64j\LaravelManagerComponents\Input;
use Team64j\LaravelManagerComponents\Panel;
use Team64j\LaravelManagerComponents\Tabs;
use Team64j\LaravelManagerComponents\Title;

class RoleLayout extends Layout
{
    public function icon(): string
    {
        return 'fa fa-legal';
    }

    public function iconList(): string
    {
        return 'fa fa-legal';
    }

    public function title(?string $value = null): string
    {
        return $value ?? __('global.role_management_title');
    }

    public function titleList(): string
    {
        return __('global.role_management_title');
    }

    public function default(?UserRole $model = null): array
    {
        return [
            GlobalTab::make()
                ->setTitle($this->title($model->name))
                ->setIcon($this->icon()),

            Title::make()
                ->setTitle($this->title($model->name))
                ->setIcon($this->icon()),

            Tabs::make()
                ->setId('role')
                ->addTab(
                    'general',
                    __('global.role'),
                    slot: [
                        Input::make('name')
                            ->setLabel(__('global.role_name'))
                            ->setAttribute('style', ['margin-bottom' => '1rem']),

                        Input::make('description')
                            ->setLabel(__('global.resource_description'))
                            ->setAttribute('style', ['margin-bottom' => '1rem']),
                    ]
                )
                ->addTab(
                    'permissions',
                    __('global.access_permissions'),
                    slot: [
                        Panel::make('data.permissions')
                            ->setId('permissions')
                            ->setUrl(api_url('permissions.index'))
                            ->addColumn(
                                'permission',
                                __('global.role_udperms'),
                                ['width' => '4rem', 'textAlign' => 'center'],
                                selectable: true,
                                component: Checkbox::make('selected_permissions')->setKeyValue('id')
                            )
                            ->addColumn(
                                'id',
                                __('global.id'),
                                ['width' => '4rem', 'textAlign' => 'center'],
                            )
                            ->addColumn(
                                'name',
                                __('global.template_name'),
                                ['fontWeight' => '500'],
                            )
                            ->addColumn(
                                'description',
                                __('global.description'),
                                ['width' => '50%'],
                            ),
                    ],
                ),
        ];
    }

    public function list(): array
    {
        return [
            GlobalTab::make()
                ->setTitle($this->titleList())
                ->setIcon($this->iconList()),

            Actions::make()
                ->setNew(
                    __('global.new_role'),
                    api_url('roles.show', [0]),
                    'btn-green'
                ),

            Title::make()
                ->setTitle($this->titleList())
                ->setIcon($this->iconList())
                ->setHelp(__('global.role_management_msg')),

            Tabs::make()
                ->setId('userManagement')
                ->setHistory(true)
                ->addTab(
                    'roles',
                    __('global.role_role_management'),
                    'fa fa-legal',
                    route: api_url('roles.index'),
                    slot: Panel::make('data')
                        ->setId('roles')
                        ->setRoute(api_url('roles.show', [':id']))
                        ->addColumn(
                            'id',
                            __('global.id'),
                            ['width' => '5rem', 'textAlign' => 'right', 'fontWeight' => 'bold']
                        )
                        ->addColumn('name', __('global.role'), ['fontWeight' => 500])
                        ->addColumn('description', __('global.description'))
                )
                ->addTab(
                    'permissions',
                    __('global.manage_permission'),
                    'fa fa-user-tag',
                    route: api_url('permissions.index'),
                )
                ->addTab(
                    'categories',
                    __('global.category_heading'),
                    'fa fa-object-group',
                    route: api_url('permissions-group.index'),
                ),
        ];
    }
}
