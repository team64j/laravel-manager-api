<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Team64j\LaravelManagerApi\Models\UserRole;
use Team64j\LaravelManagerComponents\Actions;
use Team64j\LaravelManagerComponents\Checkbox;
use Team64j\LaravelManagerComponents\GlobalTab;
use Team64j\LaravelManagerComponents\Input;
use Team64j\LaravelManagerComponents\Panel;
use Team64j\LaravelManagerComponents\Select;
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
                    api_url('roles.users', [0]),
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
                    route: api_url('roles.users.index'),
                    slot: Panel::make('data')
                        ->setId('users')
                        ->setRoute(api_url('roles.users', [':id']))
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
                    route: api_url('roles.categories.index')
                )
                ->addTab(
                    'permissions',
                    __('global.manage_permission'),
                    'fa fa-user-tag',
                    route: api_url('roles.permissions.index')
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
                ->setIcon($this->icon()),            Tabs::make()
                ->addTab('general', slot: [
                    Input::make('name')
                        ->setLabel(__('global.role_name'))
                        ->setAttribute('style', ['margin-bottom' => '1rem']),

                    Input::make('description')
                        ->setLabel(__('global.resource_description'))
                        ->setAttribute('style', ['margin-bottom' => '1rem']),

                    Checkbox::make('edit_doc_metatags')
                        ->setLabel(__('global.role_edit_doc_metatags'))
                        ->setCheckedValue(1, 0)
                        ->setAttribute('style', ['margin-bottom' => '1rem']),

                    Checkbox::make('manage_metatags')
                        ->setLabel(__('global.role_manage_metatags'))
                        ->setCheckedValue(1, 0)
                        ->setAttribute('style', ['margin-bottom' => '1rem']),
                ]),
        ];
    }
}
