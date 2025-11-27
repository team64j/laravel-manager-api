<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Team64j\LaravelManagerApi\Models\PermissionsGroups;
use Team64j\LaravelManagerComponents\Actions;
use Team64j\LaravelManagerComponents\GlobalTab;
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
    public function title(?string $value = null): string
    {
        return $value ?? __('global.new_category');
    }

    /**
     * @return string
     */
    public function titleList(): string
    {
        return __('global.role_management_title');
    }

    /**
     * @return array
     */
    public function list(): array
    {
        return [
            GlobalTab::make()
                ->setTitle($this->titleList())
                ->setIcon($this->iconList()),

            Actions::make()
                ->setNew(
                    $this->title(),
                    '/roles/categories/0',
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
                    __('global.role_role_management'),
                    $this->iconList(),
                    route: route('manager.api.roles.users.index')
                )
                ->addTab(
                    'categories',
                    __('global.category_heading'),
                    $this->icon(),
                    route: route('manager.api.roles.categories.index'),
                    slot: Panel::make('data')
                        ->setId('categories')
                        ->setRoute('/roles/categories/:id')
                        ->setHistory(true)
                        ->addColumn(
                            'id',
                            __('global.id'),
                            ['width' => '5rem', 'textAlign' => 'right', 'fontWeight' => 'bold']
                        )
                        ->addColumn('name', __('global.category_heading'), ['fontWeight' => 500])
                )
                ->addTab(
                    'permissions',
                    __('global.manage_permission'),
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
    public function default(?PermissionsGroups $model = null): array
    {
        return [
            GlobalTab::make()
                ->setTitle(
                    $this->title(trans()->has('global.' . $model->lang_key) ? __('global.' . $model->lang_key) : null)
                )
                ->setIcon($this->icon()),

            Title::make()
                ->setTitle(
                    $this->title(
                        trans()->has('global.' . $model->lang_key) ? __('global.' . $model->lang_key) : null
                    )
                )
                ->setIcon($this->icon()),
        ];
    }
}
