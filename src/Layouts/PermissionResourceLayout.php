<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Illuminate\Support\Facades\Lang;
use Team64j\LaravelManagerComponents\Actions;
use Team64j\LaravelManagerComponents\Panel;
use Team64j\LaravelManagerComponents\Tabs;
use Team64j\LaravelManagerComponents\Title;

class PermissionResourceLayout extends Layout
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
        return $value ?? Lang::get('global.manage_permission');
    }

    /**
     * @return array
     */
    public function list(): array
    {
        return [
            Actions::make()
                ->setNew(
                    Lang::get('global.create_new'),
                    '/permissions/resources/0',
                    'btn-green'
                ),

            Title::make()
                ->setTitle($this->title())
                ->setIcon($this->icon())
                ->setHelp(Lang::get('global.access_permissions_resources_tab')),

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
                    route: route('manager.api.permissions.resources'),
                    slot: Panel::make()
                        ->setModel('data')
                        ->setId('resources')
                        ->setHistory(true)
                        ->setRoute('/permissions/resources/:id')
                        ->addColumn('name', Lang::get('global.name'), ['width' => '20rem', 'fontWeight' => 500])
                        ->addColumn('documents', Lang::get('global.access_permissions_resources_in_group'))
                        ->addColumn(
                            'actions',
                            Lang::get('global.mgrlog_action'),
                            ['width' => '3rem', 'textAlign' => 'center'],
                            actions: [
                                'delete' => [
                                    'icon' => 'fa fa-trash fa-fw hover:text-rose-600',
                                    'help' => Lang::get('global.delete'),
                                    'helpFit' => true,
                                    'noOpacity' => true,
                                ],
                            ]
                        )
                )
                ->addTab(
                    'relations',
                    Lang::get('global.access_permissions_links'),
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
