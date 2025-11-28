<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Illuminate\Database\Eloquent\Collection;
use Team64j\LaravelManagerComponents\Actions;
use Team64j\LaravelManagerComponents\GlobalTab;
use Team64j\LaravelManagerComponents\Panel;
use Team64j\LaravelManagerComponents\Tabs;
use Team64j\LaravelManagerComponents\Title;

class PermissionRelationLayout extends Layout
{
    public function title(?string $value = null): string
    {
        return $value ?? __('global.manage_permission');
    }

    public function titleList(): string
    {
        return __('global.manage_permission');
    }

    public function icon(): string
    {
        return 'fa fa-male';
    }

    public function list(?Collection $groups = null, ?Collection $documents = null): array
    {
        return [
            GlobalTab::make()
                ->setTitle($model->name ?? $this->title())
                ->setIcon($this->icon()),

            Actions::make()
                ->setNew(
                    __('global.create_new'),
                    '/permissions/relations/0',
                    'btn-green'
                ),

            Title::make()
                ->setTitle($this->title())
                ->setIcon($this->icon())
                ->setHelp(__('global.access_permissions_links_tab')),

            Tabs::make()
                ->setId('permissions')
                ->setHistory(true)
                ->addTab(
                    'groups',
                    __('global.web_access_permissions_user_groups'),
                    route: route('manager.api.permissions.groups')
                )
                ->addTab(
                    'resources',
                    __('global.access_permissions_resource_groups'),
                    route: route('manager.api.permissions.resources')
                )
                ->addTab(
                    'relations',
                    __('global.access_permissions_links'),
                    route: route('manager.api.permissions.relations'),
                    slot: Panel::make('data')
                        ->setId('relations')
                        ->setHistory(true)
                        ->setRoute('/permissions/relations/:id')
                        ->addColumn('name', __('global.name'), ['width' => '20rem', 'fontWeight' => 500])
                        ->addColumn('document_groups', __('global.access_permissions_resource_groups'))
                ),
        ];
    }

    public function default($model = null): array
    {
        return [
            GlobalTab::make()
                ->setTitle($model->name ?? $this->title())
                ->setIcon($this->icon()),

            Title::make()
                ->setTitle($this->title($model->name))
                ->setIcon($this->icon()),
        ];
    }
}
