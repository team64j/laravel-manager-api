<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use EvolutionCMS\Models\Permissions;
use Illuminate\Support\Facades\Lang;
use Team64j\LaravelManagerComponents\Actions;
use Team64j\LaravelManagerComponents\Panel;
use Team64j\LaravelManagerComponents\Tabs;
use Team64j\LaravelManagerComponents\Title;

class RolePermissionLayout extends Layout
{
    /**
     * @return string
     */
    public function icon(): string
    {
        return 'fa fa-user-tag';
    }

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
        return $value ?? Lang::get('global.new_permission');
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
                    '/roles/permissions/0',
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
                    'fa fa-object-group',
                    route: route('manager.api.roles.categories.index')
                )
                ->addTab(
                    'permissions',
                    Lang::get('global.manage_permission'),
                    $this->icon(),
                    route: route('manager.api.roles.permissions.index'),
                    slot: Panel::make()
                        ->setId('permissions')
                        ->setModel('data')
                        ->setRoute('/roles/permissions/:id')
                        ->setHistory(true)
                        ->addColumn(
                            'id',
                            Lang::get('global.id'),
                            ['width' => '5rem', 'textAlign' => 'right', 'fontWeight' => 'bold']
                        )
                        ->addColumn('name', Lang::get('global.role_name'), ['fontWeight' => 500])
                        ->addColumn('key', Lang::get('global.key_desc'), ['width' => '5rem'])
                        ->addColumn(
                            'disabled',
                            Lang::get('global.disabled'),
                            ['width' => '7rem', 'textAlign' => 'center'],
                            false,
                            [
                                0 => '<span class="text-green-600">' . Lang::get('global.no') . '</span>',
                                1 => '<span class="text-rose-600">' . Lang::get('global.yes') . '</span>',
                            ]
                        )
                ),
        ];
    }

    /**
     * @param Permissions|null $model
     *
     * @return array
     */
    public function default(Permissions $model = null): array
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
