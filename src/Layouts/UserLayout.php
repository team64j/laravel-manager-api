<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Team64j\LaravelManagerApi\Models\User;
use Team64j\LaravelManagerComponents\Actions;
use Team64j\LaravelManagerComponents\Checkbox;
use Team64j\LaravelManagerComponents\DateTime;
use Team64j\LaravelManagerComponents\GlobalTab;
use Team64j\LaravelManagerComponents\Grid;
use Team64j\LaravelManagerComponents\Input;
use Team64j\LaravelManagerComponents\Panel;
use Team64j\LaravelManagerComponents\Select;
use Team64j\LaravelManagerComponents\Tabs;
use Team64j\LaravelManagerComponents\Textarea;
use Team64j\LaravelManagerComponents\Title;

class UserLayout extends Layout
{
    /**
     * @return string
     */
    public function icon(): string
    {
        return 'fa fa-user-circle';
    }

    /**
     * @return string
     */
    public function iconList(): string
    {
        return 'fa fa-users';
    }

    /**
     * @param string|null $value
     *
     * @return string
     */
    public function title(?string $value = null): string
    {
        return $value ?? __('global.new_user');
    }

    /**
     * @return string
     */
    public function titleList(): string
    {
        return __('global.users');
    }

    /**
     * @param User|null $model
     *
     * @return array
     */
    public function default(?User $model = null): array
    {
        return [
            GlobalTab::make()
                ->setTitle($this->title($model->username))
                ->setIcon($this->icon()),

            Actions::make()
                ->setCancel(
                    __('global.cancel'),
                    [
                        'path'  => '/users',
                        'close' => true,
                    ]
                )
                ->when(
                    $model->getKey(),
                    fn(Actions $actions) => $actions->setDelete()->setCopy()
                )
                ->setSaveAnd(),

            Title::make('username')
                ->setTitle($this->title())
                ->setIcon($this->icon())
                ->setId($model->getKey()),

            Tabs::make()
                ->setId('user')
                ->addTab('global', slot: [
                    Grid::make()
                        ->setGap('1.25rem')
                        ->addArea([
                            Input::make('data.username')
                                ->setLabel(__('global.username'))
                                ->isRequired()
                                ->setAttribute('style', ['margin-bottom' => '1rem']),

                            Input::make('data.attributes.fullname')
                                ->setLabel(__('global.user_full_name'))
                                ->setAttribute('style', ['margin-bottom' => '1rem']),

                            Input::make('data.attributes.first_name')
                                ->setLabel(__('global.user_first_name'))
                                ->setAttribute('style', ['margin-bottom' => '1rem']),

                            Input::make('data.attributes.middle_name')
                                ->setLabel(__('global.user_middle_name'))
                                ->setAttribute('style', ['margin-bottom' => '1rem']),

                            Select::make('data.attributes.gender')
                                ->setLabel(__('global.user_gender'))
                                ->setAttribute('style', ['margin-bottom' => '1rem'])
                                ->setData([
                                    [
                                        'key'   => 0,
                                        'value' => __('global.user_male'),
                                    ],
                                    [
                                        'key'   => 1,
                                        'value' => __('global.user_female'),
                                    ],
                                ]),

                            DateTime::make('data.attributes.dob')
                                ->setLabel(__('global.user_dob'))
                                ->setAttribute('style', ['margin-bottom' => '1rem']),

                            Input::make('data.attributes.email')
                                ->setLabel(__('global.user_email'))
                                ->setAttribute('style', ['margin-bottom' => '1rem']),

                            Textarea::make('data.attributes.comment')
                                ->setLabel(__('global.user_comment'))
                                ->setAttribute('style', ['margin-bottom' => '1rem']),

                            Input::make('data.attributes.phone')
                                ->setLabel(__('global.user_phone'))
                                ->setAttribute('style', ['margin-bottom' => '1rem']),

                            Input::make('data.attributes.mobilephone')
                                ->setLabel(__('global.user_mobilephone'))
                                ->setAttribute('style', ['margin-bottom' => '1rem']),

                            Input::make('data.attributes.fax')
                                ->setLabel(__('global.user_fax'))
                                ->setAttribute('style', ['margin-bottom' => '1rem']),

                            Input::make('data.attributes.country')
                                ->setLabel(__('global.user_country'))
                                ->setAttribute('style', ['margin-bottom' => '1rem']),

                            Input::make('data.attributes.zip')
                                ->setLabel(__('global.user_zip'))
                                ->setAttribute('style', ['margin-bottom' => '1rem']),

                            Input::make('data.attributes.city')
                                ->setLabel(__('global.user_city'))
                                ->setAttribute('style', ['margin-bottom' => '1rem']),

                            Input::make('data.attributes.state')
                                ->setLabel(__('global.user_state'))
                                ->setAttribute('style', ['margin-bottom' => '1rem']),

                            Input::make('data.attributes.street')
                                ->setLabel(__('global.user_street'))
                                ->setAttribute('style', ['margin-bottom' => '1rem']),

                        ], ['sm' => '1', 'xl' => '1 / 1 / 1 / 3'])
                        ->addArea([
                            Input::make('data.attributes.lastlogin')
                                ->setLabel(__('global.user_lastlogin'))
                                ->setAttribute('style', ['margin-bottom' => '1rem']),

                            DateTime::make('data.attributes.thislogin')
                                ->setLabel(__('global.user_thislogin'))
                                ->setAttribute('style', ['margin-bottom' => '1rem']),

                            Input::make('data.attributes.createdon')
                                ->setLabel(__('global.user_createdon'))
                                ->setAttribute('style', ['margin-bottom' => '1rem']),

                            Input::make('data.attributes.editedon')
                                ->setLabel(__('global.user_editedon'))
                                ->setAttribute('style', ['margin-bottom' => '1rem']),

                            Input::make('data.attributes.blockedafter')
                                ->setLabel(__('global.user_blockedafter'))
                                ->setAttribute('style', ['margin-bottom' => '1rem']),

                            Input::make('data.attributes.blockeduntil')
                                ->setLabel(__('global.user_blockeduntil'))
                                ->setAttribute('style', ['margin-bottom' => '1rem']),

                            Checkbox::make('data.attributes.blocked')
                                ->setLabel(__('global.user_blocked'))
                                ->setAttribute('style', ['margin-bottom' => '1rem']),
                        ], ['sm' => '2', 'xl' => '1 / 3 / 1 / 3']),
                ]),
        ];
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
                    '/users/0',
                    'btn-green'
                ),

            Title::make()
                ->setTitle($this->titleList())
                ->setIcon($this->iconList()),

            Tabs::make()
                ->setId('users')
                ->addTab(
                    'users',
                    slot: Panel::make('data')
                        ->setId('users')
                        ->setRoute('/users/:id')
                        ->setHistory(true)
                        ->addColumn(
                            'id',
                            __('global.id'),
                            ['width' => '5rem', 'textAlign' => 'right', 'fontWeight' => 'bold'],
                            true
                        )
                        ->addColumn('username', __('global.name'), ['fontWeight' => 500], true)
                        ->addColumn('fullname', __('global.user_full_name'), [], true)
                        ->addColumn('email', __('global.email'), [], true)
                        ->addColumn(['role', 'rolename'], __('global.role'), ['width' => '10rem'], true)
                        ->addColumn(
                            'lastlogin',
                            __('global.user_prevlogin'),
                            ['width' => '12rem', 'textAlign' => 'center'],
                            true
                        )
                        ->addColumn(
                            'logincount',
                            __('global.user_logincount'),
                            ['width' => '20rem', 'textAlign' => 'center'],
                            true
                        )
                        ->addColumn(
                            'blocked',
                            __('global.user_block'),
                            ['width' => '10rem', 'textAlign' => 'center'],
                            true,
                            [
                                0 => '<span class="text-success">' . __('global.no') . '</span>',
                                1 => '<span class="text-error">' . __('global.yes') . '</span>',
                            ]
                        ),
                ),
        ];
    }
}
