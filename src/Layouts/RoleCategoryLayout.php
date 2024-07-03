<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use EvolutionCMS\Models\PermissionsGroups;
use Illuminate\Support\Facades\Lang;
use Team64j\LaravelManagerComponents\Actions;
use Team64j\LaravelManagerComponents\Panel;
use Team64j\LaravelManagerComponents\Tabs;
use Team64j\LaravelManagerComponents\Title;

class RoleCategoryLayout extends Layout
{
    /**
     * @return array
     */
    public function list(): array
    {
        return [
            Actions::make()
                ->setNew(
                    Lang::get('global.new_category'),
                    '/roles/categories/new',
                    'btn-green'
                ),

            Title::make()
                ->setTitle(Lang::get('global.role_management_title'))
                ->setIcon('fa fa-legal'),

            Tabs::make()
                ->setId('userManagement')
                ->setHistory(true)
                ->addTab(
                    'users',
                    Lang::get('global.role_role_management'),
                    'fa fa-legal',
                    route: route('manager.api.roles.users.index')
                )
                ->addTab(
                    'categories',
                    Lang::get('global.category_heading'),
                    'fa fa-object-group',
                    route: route('manager.api.roles.categories.index')
                )
                ->addTab(
                    'permissions',
                    Lang::get('global.manage_permission'),
                    'fa fa-user-tag',
                    route: route('manager.api.roles.permissions.index')
                )
                ->addSlot(
                    'categories',
                    Panel::make()
                        ->setId('categories')
                        ->setModel('data')
                        ->setRoute('/roles/categories/:id')
                        ->setHistory(true)
                        ->addColumn(
                            'id',
                            Lang::get('global.id'),
                            ['width' => '5rem', 'textAlign' => 'right', 'fontWeight' => 'bold']
                        )
                        ->addColumn('name', Lang::get('global.category_heading'), ['fontWeight' => 500])
                ),
        ];
    }

    /**
     * @return string
     */
    public function getIconList(): string
    {
        return 'fa fa-legal';
    }

    /**
     * @param PermissionsGroups|null $model
     *
     * @return array
     */
    public function default(PermissionsGroups $model = null): array
    {
        return [
            Title::make()
                ->setTitle(
                    Lang::has('global.' . $model->lang_key) ? Lang::get('global.' . $model->lang_key)
                        : Lang::get('global.new_category')
                )
                ->setIcon('fa fa-object-group'),
        ];
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return 'fa fa-object-group';
    }
}
