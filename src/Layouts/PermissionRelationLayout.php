<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Lang;
use Team64j\LaravelManagerApi\Components\ActionsButtons;
use Team64j\LaravelManagerApi\Components\Panel;
use Team64j\LaravelManagerApi\Components\Tabs;
use Team64j\LaravelManagerApi\Components\Title;

class PermissionRelationLayout extends Layout
{
    /**
     * @param Collection|null $groups
     * @param Collection|null $documents
     *
     * @return array
     */
    public function list(Collection $groups = null, Collection $documents = null): array
    {
        $data[] = ActionsButtons::make()
            ->setNew(
                Lang::get('global.create_new'),
                'PermissionRelation',
                'btn-green'
            );

        $data[] = Title::make()
            ->setTitle(Lang::get('global.manage_permission'))
            ->setIcon('fa fa-male')
            ->setHelp(Lang::get('global.access_permissions_links_tab'));

        $data[] = Tabs::make()
            ->setId('permissions')
            ->setHistory('element')
            ->addTab('groups', Lang::get('global.web_access_permissions_user_groups'), null, 'py-4')
            ->addTab('resources', Lang::get('global.access_permissions_resource_groups'), null, 'py-4')
            ->addTab('relations', Lang::get('global.access_permissions_links'), null, 'py-4')
            ->addSlot(
                'relations',
                Panel::make()
                    ->setModel('data')
                    ->setId('relations')
                    ->setHistory(true)
                    ->setRoute('PermissionRelation')
                    ->addColumn('name', Lang::get('global.name'), ['width' => '20rem', 'fontWeight' => 500])
                    ->addColumn('document_groups', Lang::get('global.access_permissions_resource_groups'))
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
