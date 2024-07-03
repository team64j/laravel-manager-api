<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Illuminate\Support\Facades\Lang;
use Team64j\LaravelManagerComponents\Actions;
use Team64j\LaravelManagerComponents\Panel;
use Team64j\LaravelManagerComponents\Tabs;
use Team64j\LaravelManagerComponents\Title;

class PermissionGroupLayout extends Layout
{
    /**
     * @return array
     */
    public function list(): array
    {
        return [
            Actions::make()
                ->setNew(
                    Lang::get('global.create_new'),
                    '/permissions/groups/new',
                    'btn-green'
                ),

            Title::make()
                ->setTitle(Lang::get('global.manage_permission'))
                ->setIcon('fa fa-male')
                ->setHelp(Lang::get('global.access_permissions_users_tab')),

            Tabs::make()
                ->setId('permissions')
                ->setHistory(true)
                ->addTab(
                    'groups',
                    Lang::get('global.web_access_permissions_user_groups'),
                    route: route('manager.api.permissions.groups')
                )
                ->addTab(
                    'resources',
                    Lang::get('global.access_permissions_resource_groups'),
                    route: route('manager.api.permissions.resources')
                )
                ->addTab(
                    'relations',
                    Lang::get('global.access_permissions_links'),
                    route: route('manager.api.permissions.relations')
                )
                ->addSlot(
                    'groups',
                    Panel::make()
                        ->setModel('data')
                        ->setId('groups')
                        ->setHistory(true)
                        ->setRoute('/permissions/groups/:id')
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
                ),
        ];
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return 'fa fa-male';
    }

    /**
     * @param $model
     *
     * @return array
     */
    public function default($model = null): array
    {
        return [
            Title::make()
                ->setTitle($model->name ?: Lang::get('global.manage_permission'))
                ->setIcon($this->getIcon()),
        ];
    }
}
