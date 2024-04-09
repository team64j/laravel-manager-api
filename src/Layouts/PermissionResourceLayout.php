<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Illuminate\Support\Facades\Lang;
use Team64j\LaravelManagerApi\Components\ActionsButtons;
use Team64j\LaravelManagerApi\Components\Panel;
use Team64j\LaravelManagerApi\Components\Tabs;
use Team64j\LaravelManagerApi\Components\Title;

class PermissionResourceLayout extends Layout
{
    /**
     * @return array
     */
    public function list(): array
    {
        return [
            ActionsButtons::make()
                ->setNew(
                    Lang::get('global.create_new'),
                    'PermissionResource',
                    'btn-green'
                ),

            Title::make()
                ->setTitle(Lang::get('global.manage_permission'))
                ->setIcon('fa fa-male')
                ->setHelp(Lang::get('global.access_permissions_resources_tab')),

            Tabs::make()
                ->setId('permissions')
                ->setHistory('element')
                ->addTab('groups', Lang::get('global.web_access_permissions_user_groups'))
                ->addTab('resources', Lang::get('global.access_permissions_resource_groups'))
                ->addTab('relations', Lang::get('global.access_permissions_links'))
                ->addSlot(
                    'resources',
                    Panel::make()
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
                ->setTitle($model->name ?? Lang::get('global.manage_permission'))
                ->setIcon($this->getIcon())
        ];
    }
}
