<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use EvolutionCMS\Models\UserRole;
use Illuminate\Support\Facades\Lang;
use Team64j\LaravelManagerComponents\Actions;
use Team64j\LaravelManagerComponents\Panel;
use Team64j\LaravelManagerComponents\Tabs;
use Team64j\LaravelManagerComponents\Title;

class RoleUserLayout extends Layout
{
    /**
     * @return string
     */
    public function icon(): string
    {
        return 'fa fa-legal';
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
        return $value ?? Lang::get('global.role_management_title');
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
                    Lang::get('global.new_role'),
                    '/roles/users/new',
                    'btn-green'
                ),

            Title::make()
                ->setTitle($this->title())
                ->setIcon($this->icon())
                ->setHelp(Lang::get('global.role_management_msg')),

            Tabs::make()
                ->setId('userManagement')
                ->setHistory(true)
                ->addTab(
                    'users',
                    Lang::get('global.role_role_management'),
                    $this->iconList(),
                    route: route('manager.api.roles.users.index'),
                    slot: Panel::make()
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
                ),
        ];
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
                ->setTitle($this->title($model->name))
                ->setIcon($this->icon()),
        ];
    }
}
