<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Team64j\LaravelManagerApi\Models\UserRole;
use Team64j\LaravelManagerComponents\Actions;
use Team64j\LaravelManagerComponents\GlobalTab;
use Team64j\LaravelManagerComponents\Panel;
use Team64j\LaravelManagerComponents\Tabs;
use Team64j\LaravelManagerComponents\Title;

class RoleUserLayout extends Layout
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

    public function list(): array
    {
        return [
            GlobalTab::make()
                ->setTitle($this->titleList())
                ->setIcon($this->iconList()),

            Actions::make()
                ->setNew(
                    __('global.new_role'),
                    '/roles/users/0',
                    'btn-green'
                ),

            Title::make()
                ->setTitle($this->title())
                ->setIcon($this->icon())
                ->setHelp(__('global.role_management_msg')),

            Tabs::make()
                ->setId('userManagement')
                ->setHistory(true)
                ->addTab(
                    'users',
                    __('global.role_role_management'),
                    $this->iconList(),
                    route: route('manager.api.roles.users.index'),
                    slot: Panel::make('data')
                        ->setId('users')
                        ->setRoute('/roles/users/:id')
                        ->setHistory(true)
                        ->addColumn(
                            'id',
                            __('global.id'),
                            ['width' => '5rem', 'textAlign' => 'right', 'fontWeight' => 'bold']
                        )
                        ->addColumn('name', __('global.role'), ['fontWeight' => 500])
                        ->addColumn('description', __('global.description'))
                )
                ->addTab(
                    'categories',
                    __('global.category_heading'),
                    'fa fa-object-group',
                    route: route('manager.api.roles.categories.index')
                )
                ->addTab(
                    'permissions',
                    __('global.manage_permission'),
                    'fa fa-user-tag',
                    route: route('manager.api.roles.permissions.index')
                ),
        ];
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
        ];
    }
}
