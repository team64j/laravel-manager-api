<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Illuminate\Support\Facades\Lang;
use Team64j\LaravelEvolution\Models\Permissions;
use Team64j\LaravelManagerApi\Components\ActionsButtons;
use Team64j\LaravelManagerApi\Components\Panel;
use Team64j\LaravelManagerApi\Components\Tabs;
use Team64j\LaravelManagerApi\Components\Title;

class RolePermissionLayout extends Layout
{
    /**
     * @return array
     */
    public function list(): array
    {
        return [
            ActionsButtons::make()
                ->setNew(
                    Lang::get('global.new_permission'),
                    'RolePermission',
                    'btn-green'
                ),

            Title::make()
                ->setTitle(Lang::get('global.role_management_title'))
                ->setIcon('fa fa-legal'),

            Tabs::make()
                ->setId('userManagement')
                ->setHistory('element')
                ->addTab('users', Lang::get('global.role_role_management'), 'fa fa-legal', 'py-4')
                ->addTab('categories', Lang::get('global.category_heading'), 'fa fa-object-group', 'py-4')
                ->addTab('permissions', Lang::get('global.manage_permission'), 'fa fa-user-tag', 'py-4')
                ->addSlot(
                    'permissions',
                    Panel::make()
                        ->setId('permissions')
                        ->setModel('data')
                        ->setRoute('RolePermission')
                        ->setHistory(true)
                        ->addColumn(
                            'id',
                            Lang::get('global.id'),
                            ['width' => '5rem', 'textAlign' => 'right', 'fontWeight' => 'bold']
                        )
                        ->addColumn('name', Lang::get('global.role_name'), ['fontWeight' => 500])
                        ->addColumn('key', Lang::get('global.key_desc'), ['width' => '5rem'])
                        ->addColumn(
                            'disabled',
                            Lang::get('global.disabled'),
                            ['width' => '7rem', 'textAlign' => 'center'],
                            false,
                            [
                                0 => '<span class="text-green-600">' . Lang::get('global.no') . '</span>',
                                1 => '<span class="text-rose-600">' . Lang::get('global.yes') . '</span>',
                            ]
                        )
                ),
        ];
    }

    /**
     * @return array
     */
    public function titleList(): array
    {
        return [
            'title' => Lang::get('global.role_management_title'),
            'icon' => 'fa fa-legal',
        ];
    }

    /**
     * @param Permissions|null $model
     *
     * @return array
     */
    public function default(Permissions $model = null): array
    {
        return [
            Title::make()
                ->setTitle(
                    Lang::has('global.' . $model->lang_key) ? Lang::get('global.' . $model->lang_key)
                        : Lang::get('global.new_permission')
                )
                ->setIcon('fa fa-user-tag')
        ];
    }

    /**
     * @param Permissions|null $model
     *
     * @return array
     */
    public function titleDefault(Permissions $model = null): array
    {
        return [
            'title' => Lang::has('global.' . $model->lang_key) ? Lang::get('global.' . $model->lang_key)
                : Lang::get('global.new_permission'),
            'icon' => 'fa fa-user-tag',
        ];
    }
}
