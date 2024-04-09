<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use EvolutionCMS\Models\UserRole;
use Illuminate\Support\Facades\Lang;
use Team64j\LaravelManagerApi\Components\ActionsButtons;
use Team64j\LaravelManagerApi\Components\Panel;
use Team64j\LaravelManagerApi\Components\Tabs;
use Team64j\LaravelManagerApi\Components\Title;

class RoleUserLayout extends Layout
{
    /**
     * @return array
     */
    public function list(): array
    {
        return [
            ActionsButtons::make()
                ->setNew(
                    Lang::get('global.new_role'),
                    'RoleUser',
                    'btn-green'
                ),

            Title::make()
                ->setTitle(Lang::get('global.role_management_title'))
                ->setIcon('fa fa-legal')
                ->setHelp(Lang::get('global.role_management_msg')),

            Tabs::make()
                ->setId('userManagement')
                ->setHistory('element')
                ->addTab('users', Lang::get('global.role_role_management'), 'fa fa-legal')
                ->addTab('categories', Lang::get('global.category_heading'), 'fa fa-object-group')
                ->addTab('permissions', Lang::get('global.manage_permission'), 'fa fa-user-tag')
                ->addSlot(
                    'users',
                    Panel::make()
                        ->setId('users')
                        ->setModel('data')
                        ->setRoute('/roles/users/:id')
                        ->setHistory(true)
                        ->addColumn(
                            'id',
                            Lang::get('global.id'),
                            ['width' => '5rem', 'textAlign' => 'right', 'fontWeight' => 'bold']
                        )
                        ->addColumn('name', Lang::get('global.role'), ['fontWeight' => 500])
                        ->addColumn('description', Lang::get('global.description'))
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
     * @param UserRole|null $model
     *
     * @return array
     */
    public function default(UserRole $model = null): array
    {
        return [
            Title::make()
                ->setTitle($model->name ?: Lang::get('global.new_role'))
                ->setIcon('fa fa-legal')
        ];
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return 'fa fa-legal';
    }
}
