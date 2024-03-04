<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Illuminate\Support\Facades\Lang;
use Team64j\LaravelManagerApi\Components\Panel;
use Team64j\LaravelManagerApi\Components\Tab;
use Team64j\LaravelManagerApi\Components\Title;
use Team64j\LaravelManagerApi\Components\Tree;

class FilesLayout extends Layout
{
    /**
     * @return array
     */
    public function default(): array
    {
        return [
            Title::make()
                ->setTitle(Lang::get('global.files_management'))
                ->setIcon($this->getIcon()),

            Panel::make()
                ->setId('FilesPanel')
                ->setModel('data')
                ->isRerender(),
        ];
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return 'far fa-folder-open';
    }

    /**
     * @return array
     */
    public function tree(): array
    {
        return Tab::make()
            ->setId('files')
            ->setIcon('fa fa-folder-open')
            ->setTitle(Lang::get('global.files_files'))
            ->setPermissions(['file_manager'])
            ->setRoute('File')
            ->isNeedUpdate()
            ->setSlot(
                Tree::make()
                    ->setId('treeFiles')
                    ->setRoute('File')
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
                            Lang::get('global.createdon') . ': {date}' . PHP_EOL .
                            Lang::get('global.files_filesize') . ': {size}' . PHP_EOL,
                    ])
                    ->setSettings([
                        'parent' => 'Lw==',
                        'show' => ['_date'],
                    ])
            )
            ->toArray();
    }
}
