<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use EvolutionCMS\Models\PermissionsGroups;
use Illuminate\Support\Facades\Lang;
use Team64j\LaravelManagerApi\Components\ActionsButtons;
use Team64j\LaravelManagerApi\Components\Panel;
use Team64j\LaravelManagerApi\Components\Tabs;
use Team64j\LaravelManagerApi\Components\Title;

class RoleCategoryLayout extends Layout
{
    /**
     * @return array
     */
    public function list(): array
    {
        return [
            ActionsButtons::make()
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
                ->setHistory('element')
                ->addTab('users', Lang::get('global.role_role_management'), 'fa fa-legal')
                ->addTab('categories', Lang::get('global.category_heading'), 'fa fa-object-group')
                ->addTab('permissions', Lang::get('global.manage_permission'), 'fa fa-user-tag')
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
