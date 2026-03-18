<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Team64j\LaravelManagerApi\Models\Permissions;
use Team64j\LaravelManagerComponents\Actions;
use Team64j\LaravelManagerComponents\Checkbox;
use Team64j\LaravelManagerComponents\GlobalTab;
use Team64j\LaravelManagerComponents\Input;
use Team64j\LaravelManagerComponents\Panel;
use Team64j\LaravelManagerComponents\Select;
use Team64j\LaravelManagerComponents\Tabs;
use Team64j\LaravelManagerComponents\Title;

class PermissionLayout extends Layout
{
    public function icon(): string
    {
        return 'fa fa-user-tag';
    }

    public function iconList(): string
    {
        return 'fa fa-legal';
    }

    public function title(?string $value = null): string
    {
        return $value ?? __('global.new_permission');
    }

    public function titleList(): string
    {
        return __('global.manage_permission');
    }

    public function default(?Permissions $model = null): array
    {
        return [
            GlobalTab::make()
                ->setTitle(
                    $this->title(trans()->has('global.' . $model->lang_key) ? __('global.' . $model->lang_key) : null)
                )
                ->setIcon($this->icon()),

            Title::make()
                ->setTitle(
                    $this->title(
                        trans()->has('global.' . $model->lang_key) ? __('global.' . $model->lang_key) : null
                    )
                )
                ->setIcon($this->icon()),

            Tabs::make()
                ->addTab('general', slot: [
                    Input::make('name')
                        ->setLabel(__('global.role_name'))
                        ->setAttribute('style', ['margin-bottom' => '1rem']),

                    Input::make('key')
                        ->setLabel(__('global.key_desc'))
                        ->setAttribute('style', ['margin-bottom' => '1rem']),

                    Input::make('lang_key')
                        ->setLabel(__('global.lang_key_desc'))
                        ->setAttribute('style', ['margin-bottom' => '1rem']),

                    Select::make('group_id')
                        ->setLabel(__('global.existing_category'))
                        ->setUrl(api_url('permissions.group.select'))
                        ->isLoad()
                        ->setAttribute('style', ['margin-bottom' => '1rem']),

                    Checkbox::make('disabled')
                        ->setLabel(__('global.disabled'))
                        ->setCheckedValue(1, 0),
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
                    api_url('permissions.show', [0]),
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
                    slot: Panel::make('data')
                        ->setId('permissions')
                        ->setRoute(api_url('permissions.show', [':id']))
                        ->addColumn(
                            'id',
                            __('global.id'),
                            ['width' => '5rem', 'textAlign' => 'right', 'fontWeight' => 'bold']
                        )
                        ->addColumn('name', __('global.role_name'), ['fontWeight' => 500])
                        ->addColumn('key', __('global.key_desc'), ['width' => '5rem'])
                        ->addColumn(
                            'disabled',
                            __('global.disabled'),
                            ['width' => '7rem', 'textAlign' => 'center'],
                            false,
                            [
                                0 => '<span class="text-success">' . __('global.no') . '</span>',
                                1 => '<span class="text-error">' . __('global.yes') . '</span>',
                            ]
                        )
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
