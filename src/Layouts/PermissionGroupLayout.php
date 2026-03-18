<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Team64j\LaravelManagerComponents\Actions;
use Team64j\LaravelManagerComponents\GlobalTab;
use Team64j\LaravelManagerComponents\Input;
use Team64j\LaravelManagerComponents\Panel;
use Team64j\LaravelManagerComponents\Tabs;
use Team64j\LaravelManagerComponents\Title;

class PermissionGroupLayout extends Layout
{
    public function icon(): string
    {
        return 'fa fa-male';
    }

    public function iconList(): string
    {
        return 'fa fa-legal';
    }

    public function title(?string $value = null): string
    {
        return $value ?? __('global.new_category');
    }

    public function titleList(): string
    {
        return __('global.category_heading');
    }

    public function default($model = null): array
    {
        return [
            GlobalTab::make()
                ->setTitle($model->name ?? $this->title())
                ->setIcon($this->icon()),

            Title::make()
                ->setTitle($this->title($model->name))
                ->setIcon($this->icon()),

            Tabs::make()
                ->addTab('general', slot: [
                    Input::make('name')
                        ->setLabel(__('global.cm_category_name'))
                        ->setAttribute('style', ['margin-bottom' => '1rem']),

                    Input::make('lang_key')
                        ->setLabel(__('global.lang_key_desc'))
                        ->setAttribute('style', ['margin-bottom' => '1rem']),
                ]),
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
                    $this->title(),
                    api_url('permissions-group.show', [0]),
                    'btn-green'
                ),

            Title::make()
                ->setTitle($this->titleList())
                ->setIcon($this->iconList()),

            Tabs::make()
                ->setId('userManagement')
                ->setHistory(true)
                ->addTab(
                    'roles',
                    __('global.role_role_management'),
                    'fa fa-legal',
                    route: api_url('roles.index'),
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
                    slot: Panel::make('data')
                        ->setId('categories')
                        ->setRoute(api_url('permissions-group.show', [':id']))
                        ->addColumn(
                            'id',
                            __('global.id'),
                            ['width' => '5rem', 'textAlign' => 'right', 'fontWeight' => 'bold']
                        )
                        ->addColumn('name', __('global.category_heading'), ['fontWeight' => 500])
                ),
        ];
    }
}
