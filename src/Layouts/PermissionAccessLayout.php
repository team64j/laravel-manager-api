<?php

namespace Team64j\LaravelManagerApi\Layouts;

use Team64j\LaravelManagerComponents\Button;
use Team64j\LaravelManagerComponents\GlobalTab;
use Team64j\LaravelManagerComponents\Grid;
use Team64j\LaravelManagerComponents\Input;
use Team64j\LaravelManagerComponents\Panel;
use Team64j\LaravelManagerComponents\Tabs;
use Team64j\LaravelManagerComponents\Title;

class PermissionAccessLayout extends Layout
{
    public function default(): array
    {
        return [];
    }

    public function title(): string
    {
        return 'PermissionAccess';
    }

    public function icon(): string
    {
        return 'fa fa-male';
    }

    public function users(): array
    {
        return [
            GlobalTab::make()
                ->setTitle(__('global.access_permissions_user_groups'))
                ->setIcon($this->icon()),

            Title::make()
                ->setTitle(__('global.web_access_permissions'))
                ->setIcon($this->icon())
                ->setHelp(__('global.access_permissions_introtext')),

            Tabs::make()
                ->setId('permission_access')
                ->setHistory(true)
                ->addTab(
                    'users',
                    __('global.access_permissions_user_groups'),
                    route: route('manager.api.permissions.access.users.show'),
                    slot: [
                        Panel::make('data')
                            ->setId('data')
                            ->setAttribute('style', ['padding-bottom' => '1rem'])
                            ->setSlotTop(
                                [
                                    '<div class="app-alert app-alert__warning" style="margin: 1rem 1.5rem 0">' . __(
                                        'global.access_permissions_users_tab'
                                    )
                                    . '</div>',
                                    Grid::make()
                                        ->setGap('0.5rem')
                                        ->setAttribute(
                                            'style',
                                            [
                                                'margin-bottom' => '1rem',
                                                'padding' => '1rem 1.5rem',
                                                'grid-template-columns' => 'auto auto auto 10rem',
                                            ]
                                        )
                                        ->addArea(
                                            [
                                                __('global.access_permissions_add_user_group'),
                                            ],
                                            '1 / 1 / 2 / 5'
                                        )
                                        ->addArea(
                                            [
                                                Input::make('new_group'),
                                            ],
                                            '2 / 1 / 3 / 4'
                                        )
                                        ->addArea(
                                            [
                                                Button::make()
                                                    ->setValue(__('global.submit'))
                                                    ->setInputClass('btn-green w-full text-center'),
                                            ],
                                            '2 / 4 / 3 / 5'
                                        ),
                                ]
                            )
                            /*->addColumn(
                                'id',
                                __('global.id'),
                                ['width' => '5rem', 'textAlign' => 'right', 'fontWeight' => 'bold']
                            )*/
                            ->addColumn(
                                '',
                                selectable: true,
                                component: Input::make()
                                    ->setInputClass('input-sm')
                                    ->setKeyValue('name')
                            )
                            ->addColumn(
                                '',
                                //__('global.save'),
                                style: ['width' => '1%'],
                                component: Button::make()
                                    ->setInputClass('btn-sm btn-green w-full text-center')
                                    ->setValue(__('global.users_list'))
                            )
                            ->addColumn(
                                '',
                                //__('global.save'),
                                style: ['width' => '1%'],
                                component: Button::make()
                                    ->setInputClass('btn-sm btn-blue w-full text-center')
                                    ->setValue(__('global.rename'))
                            )
                            ->addColumn(
                                '',
                                //__('global.remove'),
                                style: ['width' => '1%'],
                                component: Button::make()
                                    ->setInputClass('btn-sm btn-red w-full text-center')
                                    ->setValue(__('global.remove'))
                            )
                        /*->addColumn('name', __('global.name'), ['fontWeight' => 500])*/,
                    ]
                )
                ->addTab(
                    'resources',
                    __('global.access_permissions_resource_groups'),
                    route: route('manager.api.permissions.access.resources.show')
                )
                ->addTab(
                    'relations',
                    __('global.access_permissions_links'),
                    route: route('manager.api.permissions.access.relations.show')
                ),
        ];
    }

    public function resources(): array
    {
        return [
            GlobalTab::make()
                ->setTitle(__('global.access_permissions_resource_groups'))
                ->setIcon($this->icon()),

            Title::make()
                ->setTitle(__('global.web_access_permissions'))
                ->setIcon($this->icon())
                ->setHelp(__('global.access_permissions_introtext')),

            Tabs::make()
                ->setId('permission_access')
                ->setHistory(true)
                ->addTab(
                    'users',
                    __('global.access_permissions_user_groups'),
                    route: route('manager.api.permissions.access.users.show')
                )
                ->addTab(
                    'resources',
                    __('global.access_permissions_resource_groups'),
                    route: route('manager.api.permissions.access.resources.show'),
                    slot: [
                        '<div class="app-alert app-alert__warning">' . __('global.access_permissions_resources_tab')
                        . '</div>',
                    ]
                )
                ->addTab(
                    'relations',
                    __('global.access_permissions_links'),
                    route: route('manager.api.permissions.access.relations.show')
                ),
        ];
    }

    public function relations(): array
    {
        return [
            GlobalTab::make()
                ->setTitle(__('global.access_permissions_links'))
                ->setIcon($this->icon()),

            Title::make()
                ->setTitle(__('global.web_access_permissions'))
                ->setIcon($this->icon())
                ->setHelp(__('global.access_permissions_introtext')),

            Tabs::make()
                ->setId('permission_access')
                ->setHistory(true)
                ->addTab(
                    'users',
                    __('global.access_permissions_user_groups'),
                    route: route('manager.api.permissions.access.users.show')
                )
                ->addTab(
                    'resources',
                    __('global.access_permissions_resource_groups'),
                    route: route('manager.api.permissions.access.resources.show')
                )
                ->addTab(
                    'relations',
                    __('global.access_permissions_links'),
                    route: route('manager.api.permissions.access.relations.show'),
                    slot: [
                        '<div class="app-alert app-alert__warning">' . __('global.access_permissions_links_tab')
                        . '</div>',
                    ]
                ),
        ];
    }
}
