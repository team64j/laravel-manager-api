<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Team64j\LaravelManagerApi\Models\Permissions;
use Team64j\LaravelManagerComponents\Actions;
use Team64j\LaravelManagerComponents\Panel;
use Team64j\LaravelManagerComponents\Tabs;
use Team64j\LaravelManagerComponents\Title;

class RolePermissionLayout extends Layout
{
    /**
     * @return string
     */
    public function icon(): string
    {
        return 'fa fa-user-tag';
    }

    public function iconList(): string
    {
        return 'fa fa-legal';
    }

    /**
     * @param string|null $value
     *
     * @return string
     */
    public function title(?string $value = null): string
    {
        return $value ?? __('global.new_permission');
    }

    /**
     * @return string
     */
    public function titleList(): string
    {
        return __('global.role_management_title');
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
                    '/roles/permissions/0',
                    'btn-green'
                ),

            Title::make()
                ->setTitle($this->titleList())
                ->setIcon($this->iconList()),

            Tabs::make()
                ->setId('userManagement')
                ->setClass('px-4 pb-4')
                ->setHistory(true)
                ->addTab(
                    'users',
                    __('global.role_role_management'),
                    $this->iconList(),
                    route: route('manager.api.roles.users.index')
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
                    $this->icon(),
                    route: route('manager.api.roles.permissions.index'),
                    slot: Panel::make('data')
                        ->setId('permissions')
                        ->setRoute('/roles/permissions/:id')
                        ->setHistory(true)
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
                                0 => '<span class="text-green-600">' . __('global.no') . '</span>',
                                1 => '<span class="text-rose-600">' . __('global.yes') . '</span>',
                            ]
                        )
                ),
        ];
    }

    /**
     * @param Permissions|null $model
     *
     * @return array
     */
    public function default(?Permissions $model = null): array
    {
        return [
            Title::make()
                ->setTitle(
                    $this->title(
                        trans()->has('global.' . $model->lang_key) ? __('global.' . $model->lang_key) : null
                    )
                )
                ->setIcon($this->icon()),
        ];
    }
}
