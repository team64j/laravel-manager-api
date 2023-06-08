<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Illuminate\Support\Facades\Lang;
use Team64j\LaravelEvolution\Models\UserRole;
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
        $data[] = ActionsButtons::make()
            ->setNew(
                Lang::get('global.new_role'),
                'RoleUser',
                'btn-green'
            );

        $data[] = Title::make()
            ->setTitle(Lang::get('global.role_management_title'))
            ->setIcon('fa fa-legal')
            ->setHelp(Lang::get('global.role_management_msg'));

        $data[] = Tabs::make()
            ->setId('userManagement')
            ->setHistory('element')
            ->addTab('users', Lang::get('global.role_role_management'), 'fa fa-legal', 'py-4')
            ->addTab('categories', Lang::get('global.category_heading'), 'fa fa-object-group', 'py-4')
            ->addTab('permissions', Lang::get('global.manage_permission'), 'fa fa-user-tag', 'py-4')
            ->addSlot(
                'users',
                Panel::make()
                    ->setId('users')
                    ->setModel('data')
                    ->setRoute('RoleUser')
                    ->setHistory(true)
                    ->addColumn(
                        'id',
                        Lang::get('global.id'),
                        ['width' => '5rem', 'textAlign' => 'right', 'fontWeight' => 'bold']
                    )
                    ->addColumn('name', Lang::get('global.role'), ['fontWeight' => 500])
                    ->addColumn('description', Lang::get('global.description'))
            );

        return $data;
    }

    /**
     * @return array
     */
    public function titleList(): array
    {
        return [
            'title' => Lang::get('global.role_management_title'),
            'icon' => 'fa fa-legal',
        ];
    }

    /**
     * @param UserRole|null $model
     *
     * @return array
     */
    public function default(UserRole $model = null): array
    {
        $data[] = Title::make()
            ->setTitle($model->name ?: Lang::get('global.new_role'))
            ->setIcon('fa fa-legal');

        return $data;
    }

    /**
     * @param UserRole|null $model
     *
     * @return array
     */
    public function titleDefault(UserRole $model = null): array
    {
        return [
            'title' => $model->name ?: Lang::get('global.new_role'),
            'icon' => 'fa fa-legal',
        ];
    }
}
