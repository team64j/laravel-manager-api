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
     * @return string
     */
    public function icon(): string
    {
        return 'fa fa-object-group';
    }

    /**
     * @return string
     */
    public function iconList(): string
    {
        return 'fa fa-legal';
    }

    /**
     * @param string|null $value
     *
     * @return string
     */
    public function title(string $value = null): string
    {
        return $value ?? Lang::get('global.new_category');
    }

    /**
     * @return string
     */
    public function titleList(): string
    {
        return Lang::get('global.role_management_title');
    }

    /**
     * @return array
     */
    public function list(): array
    {
        return [
            Actions::make()
                ->setNew(
                    $this->title(),
                    '/roles/categories/new',
                    'btn-green'
                ),

            Title::make()
                ->setTitle($this->titleList())
                ->setIcon($this->iconList()),

            Tabs::make()
                ->setId('userManagement')
                ->setHistory(true)
                ->addTab(
                    'users',
                    Lang::get('global.role_role_management'),
                    $this->iconList(),
                    route: route('manager.api.roles.users.index')
                )
                ->addTab(
                    'categories',
                    Lang::get('global.category_heading'),
                    $this->icon(),
                    route: route('manager.api.roles.categories.index'),
                    slot: Panel::make()
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
                )
                ->addTab(
                    'permissions',
                    Lang::get('global.manage_permission'),
                    'fa fa-user-tag',
                    route: route('manager.api.roles.permissions.index')
                ),
        ];
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
                    $this->title(
                        Lang::has('global.' . $model->lang_key) ? Lang::get('global.' . $model->lang_key) : null
                    )
                )
                ->setIcon($this->icon()),
        ];
    }
}
