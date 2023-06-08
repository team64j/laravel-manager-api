<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Illuminate\Support\Facades\Lang;
use Team64j\LaravelManagerApi\Components\ActionsButtons;
use Team64j\LaravelManagerApi\Components\Panel;
use Team64j\LaravelManagerApi\Components\Tabs;
use Team64j\LaravelManagerApi\Components\Title;

class PermissionGroupLayout extends Layout
{
    /**
     * @return array
     */
    public function list(): array
    {
        $data[] = ActionsButtons::make()
            ->setNew(
                Lang::get('global.create_new'),
                'PermissionGroup',
                'btn-green'
            );

        $data[] = Title::make()
            ->setTitle(Lang::get('global.manage_permission'))
            ->setIcon('fa fa-male')
            ->setHelp(Lang::get('global.access_permissions_users_tab'));

        $data[] = Tabs::make()
            ->setId('permissions')
            ->setHistory('element')
            ->addTab('groups', Lang::get('global.web_access_permissions_user_groups'), null, 'py-4')
            ->addTab('resources', Lang::get('global.access_permissions_resource_groups'), null, 'py-4')
            ->addTab('relations', Lang::get('global.access_permissions_links'), null, 'py-4')
            ->addSlot(
                'groups',
                Panel::make()
                    ->setModel('data')
                    ->setId('groups')
                    ->setHistory(true)
                    ->setRoute('PermissionGroup')
                    ->addColumn('name', Lang::get('global.name'), ['width' => '20rem', 'fontWeight' => 500])
                    ->addColumn('users', Lang::get('global.access_permissions_users_in_group'))
                    ->addColumn(
                        'actions',
                        Lang::get('global.mgrlog_action'),
                        ['width' => '3rem', 'textAlign' => 'center'],
                        false,
                        [],
                        [
                            'delete' => [
                                'icon' => 'fa fa-trash fa-fw hover:text-rose-600',
                                'help' => Lang::get('global.delete'),
                                'helpFit' => true,
                                'noOpacity' => true,
                            ],
                        ]
                    )
            );

        return $data;
    }

    /**
     * @return array
     */
    public function titleList(): array
    {
        return [
            'title' => Lang::get('global.manage_permission'),
            'icon' => 'fa fa-male',
        ];
    }

    /**
     * @param $model
     *
     * @return array
     */
    public function default($model = null): array
    {
        $data[] = Title::make()
            ->setTitle($model->name ?: Lang::get('global.manage_permission'))
            ->setIcon('fa fa-male');

        return $data;
    }

    /**
     * @param $model
     *
     * @return array
     */
    public function titleDefault($model = null): array
    {
        return [
            'title' => $model->name ?: Lang::get('global.manage_permission'),
            'icon' => 'fa fa-male',
        ];
    }
}
