<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Team64j\LaravelManagerComponents\Actions;
use Team64j\LaravelManagerComponents\Panel;
use Team64j\LaravelManagerComponents\Tabs;
use Team64j\LaravelManagerComponents\Title;

class PermissionGroupLayout extends Layout
{
    /**
     * @return string
     */
    public function icon(): string
    {
        return 'fa fa-male';
    }

    /**
     * @param string|null $value
     *
     * @return string
     */
    public function title(string $value = null): string
    {
        return $value ?? __('global.manage_permission');
    }

    /**
     * @return array
     */
    public function list(): array
    {
        return [
            Actions::make()
                ->setNew(
                    __('global.create_new'),
                    '/permissions/groups/0',
                    'btn-green'
                ),

            Title::make()
                ->setTitle($this->title())
                ->setIcon($this->icon())
                ->setHelp(__('global.access_permissions_users_tab')),

            Tabs::make()
                ->setId('permissions')
                ->setClass('px-4 pb-4')
                ->setHistory(true)
                ->addTab(
                    'groups',
                    __('global.web_access_permissions_user_groups'),
                    route: route('manager.api.permissions.groups'),
                    slot: Panel::make()
                    ->setModel('data')
                    ->setId('groups')
                    ->setHistory(true)
                    ->setRoute('/permissions/groups/:id')
                        ->addColumn('name', __('global.name'), ['width' => '20rem', 'fontWeight' => 500])
                        ->addColumn('users', __('global.access_permissions_users_in_group'))
                    ->addColumn(
                        'actions',
                        __('global.mgrlog_action'),
                        ['width' => '3rem', 'textAlign' => 'center'],
                        false,
                        [],
                        [
                            'delete' => [
                                'icon' => 'fa fa-trash fa-fw hover:text-rose-600',
                                'help' => __('global.delete'),
                                'helpFit' => true,
                                'noOpacity' => true,
                            ],
                        ]
                    )
                )
                ->addTab(
                    'resources',
                    __('global.access_permissions_resource_groups'),
                    route: route('manager.api.permissions.resources')
                )
                ->addTab(
                    'relations',
                    __('global.access_permissions_links'),
                    route: route('manager.api.permissions.relations')
                ),
        ];
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
                ->setTitle($this->title($model->name))
                ->setIcon($this->icon()),
        ];
    }
}
