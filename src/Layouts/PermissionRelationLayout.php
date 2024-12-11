<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Illuminate\Database\Eloquent\Collection;
use Team64j\LaravelManagerComponents\Actions;
use Team64j\LaravelManagerComponents\Panel;
use Team64j\LaravelManagerComponents\Tabs;
use Team64j\LaravelManagerComponents\Title;

class PermissionRelationLayout extends Layout
{
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
     * @return string
     */
    public function titleList(): string
    {
        return __('global.manage_permission');
    }

    /**
     * @return string
     */
    public function icon(): string
    {
        return 'fa fa-male';
    }

    /**
     * @param Collection|null $groups
     * @param Collection|null $documents
     *
     * @return array
     */
    public function list(Collection $groups = null, Collection $documents = null): array
    {
        return [
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
                    slot: Panel::make()
                    ->setModel('data')
                    ->setId('relations')
                    ->setHistory(true)
                    ->setRoute('/permissions/relations/:id')
                        ->addColumn('name', __('global.name'), ['width' => '20rem', 'fontWeight' => 500])
                        ->addColumn('document_groups', __('global.access_permissions_resource_groups'))
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
