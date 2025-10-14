<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Team64j\LaravelManagerComponents\Main;
use Team64j\LaravelManagerComponents\Panel;
use Team64j\LaravelManagerComponents\Tab;
use Team64j\LaravelManagerComponents\Title;
use Team64j\LaravelManagerComponents\Tree;

class FilemanagerLayout extends Layout
{
    /**
     * @return string
     */
    public function title(): string
    {
        return __('global.settings_misc');
    }

    /**
     * @return string
     */
    public function icon(): string
    {
        return 'far fa-folder-open';
    }

    /**
     * @return array
     */
    public function default(): array
    {
        return [
            Main::make([
                'title' => Title::make()
                    ->setTitle($this->title())
                    ->setIcon($this->icon()),

                'sidebar' => [
                    Tree::make()
                        ->setId('filemanagerTree')
                        ->setUrl('/filemanager/tree')
                        ->setRoute([
                            'path' => '/filemanager/:key',
                        ])
                        ->setIcons([
                            'default-folder' => 'fa fa-folder',
                            'default-folder-open' => $this->icon(),
                        ])
                        ->setSettings([
                            'keyId' => 'key',
                            'history' => 'key',
                        ]),
                ],

                'main' => [
                    Panel::make('data')
                        ->setId('filemanagerPanel')
                        //->setUrl('/filemanager/:key')
                        ->setHistory('key')
                        ->setView('list')
                        ->setViews([
                            [
                                'key' => 'icon',
                                //'value' => 'Icons',
                                'icon' => 'fas fa-image'
                            ],
                            [
                                'key' => 'list',
                                //'value' => 'List',
                                'icon' => 'fas fa-list'
                            ],
                        ])
                        ->setColumns([
                            [
                                'name' => 'icon',
                                'label' => __('global.icon'),
                                'width' => '2rem',
                                'style' => [
                                    'textAlign' => 'center',
                                    'flexGrow' => '1',
                                    'alignItems' => 'center',
                                    'justifyContent' => 'center',
                                ],
                                'values' => [
                                    'folder' => [
                                        'false' => '<i class="far fa-file fa-fw"></i>',
                                        'true' => '<i class="fa fa-folder fa-fw"></i>',
                                    ],
                                ],
                            ],
                            [
                                'name' => 'title',
                                'label' => __('global.files_filename'),
                            ],
                            [
                                'name' => 'size',
                                'label' => __('global.files_filesize'),
                                'width' => '12rem',
                                'style' => [
                                    'textAlign' => 'right',
                                ],
                            ],
                            [
                                'name' => 'date',
                                'label' => __('global.datechanged'),
                                'width' => '12rem',
                                'style' => [
                                    'textAlign' => 'right',
                                    'whiteSpace' => 'nowrap',
                                ],
                            ],
                        ])
                        ->setContextMenu([
                            'class' => 'text-base',
                            'actions' => [
                                [
                                    'title' => __('global.preview'),
                                    'icon' => 'fa fa-eye',
                                    'route' => [
                                        'path' => '/preview/:id',
                                        'target' => '_blank',
                                    ],
                                ],
                                [
                                    'title' => __('global.download'),
                                    'icon' => 'fa fa-download',
                                    'route' => [
                                        'path' => '/download/:id',
                                    ],
                                ],
                                [
                                    'split' => true,
                                ],
                                [
                                    'title' => __('global.duplicate'),
                                    'icon' => 'fa fa-copy',
                                    'route' => [
                                        'path' => '/duplicate/:id',
                                    ],
                                ],
                                [
                                    'split' => true,
                                ],
                                [
                                    'title' => __('global.rename'),
                                    'icon' => 'fa fa-i-cursor',
                                    'route' => [
                                        'path' => '/rename/:id',
                                    ],
                                ],
                                [
                                    'title' => __('global.delete'),
                                    'icon' => 'fa fa-trash',
                                    'route' => [
                                        'path' => '/delete/:id',
                                    ],
                                ],
                            ],
                        ]),

                ],
            ]),
        ];
    }

    /**
     * @return array
     */
    public function tree(): array
    {
        return Tab::make()
            ->setId('files')
            ->setIcon($this->icon())
            ->setTitle(__('global.files_files'))
            ->setPermissions(['file_manager'])
            ->setRoute('/files/:id')
            ->isNeedUpdate()
            ->setSlot(
                Tree::make()
                    ->setId('treeFiles')
                    ->setRoute('/file/:id')
                    ->setUrl('/file/tree')
                    ->isCategory()
                    ->setIcons([
                        'default' => 'fa fa-ban',
                        'htm' => 'fa fa-code',
                        'htm' => 'fa fa-code',
                        'html' => 'fa fa-code',
                        'phtml' => 'fa fa-code',
                        'txt' => 'fa fa-code',
                        'css' => 'fa fa-code text-blue-500',
                        'less' => 'fa fa-code text-blue-500',
                        'cass' => 'fa fa-code text-blue-500',
                        'php' => 'fab fa-php text-purple-500',
                        'vue' => 'fab fa-vuejs text-emerald-500',
                        'ts' => 'fa fa-code text-green-500',
                        'mjs' => 'fa fa-code text-green-600',
                        'cjs' => 'fa fa-code text-green-600',
                        'js' => 'fa fa-code text-green-500',
                        'json' => 'fa fa-code text-green-500',
                        'xml' => 'fa fa-code text-green-500',
                        'yml' => 'fa fa-code',
                        'svg' => 'far fa-image',
                        'webp' => 'far fa-image',
                        'jpg' => 'far fa-image',
                        'jpeg' => 'far fa-image',
                        'png' => 'far fa-image',
                        'gif' => 'far fa-image',
                        'lock' => 'fa fa-lock text-rose-500',
                        'bat' => 'fa fa-file-code text-rose-800',
                        'md' => 'fa fa-code',
                        'artisan' => 'fa fa-code text-blue-500',
                        'htaccess' => 'fa fa-code',
                        'gitignore' => 'fab fa-git text-orange-700',
                        'gitattributes' => 'fab fa-git text-orange-700',
                        'env' => 'fa fa-code',
                        'editorconfig' => 'fa fa-code',
                        //'default' => 'far fa-file',
                        //                    'text/html' => 'far fa-file',
                        //                    'text/plain' => 'far fa-file',
                        //                    'text/x-php' => 'far fa-file',
                        //                    'text/x-java' => 'far fa-file',
                        //                    'text/x-js' => 'far fa-file',
                        //                    'text/xml' => 'far fa-file',
                        //                    'application/json' => 'far fa-file',
                    ])
                    ->setMenu([
                        'actions' => [
                            [
                                'icon' => 'fa fa-refresh',
                                'click' => 'update',
                                'loader' => true,
                            ],
                            [
                                'icon' => 'fa fa-ellipsis-vertical',
                                'position' => 'right',
                                'actions' => [
                                    [
                                        'key' => 'show',
                                        'value' => '_date',
                                        'title' => 'Показывать дату',
                                        'toggle' => true,
                                    ],
                                    [
                                        'key' => 'show',
                                        'value' => '_size',
                                        'title' => 'Показывать размер',
                                        'toggle' => true,
                                    ],
                                ],
                            ],
                        ],
                    ])
                    ->setAppends(['_size', '_date'])
                    ->setTemplates([
                        'title' =>
                            '{title}' . PHP_EOL .
                            __('global.createdon') . ': {date}' . PHP_EOL .
                            __('global.files_filesize') . ': {size}' . PHP_EOL,
                    ])
                    ->setSettings([
                        'parent' => 'Lw==',
                        'show' => ['_date'],
                    ])
            )
            ->toArray();
    }
}
