<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Illuminate\Support\Facades\Lang;
use Team64j\LaravelEvolution\Models\SiteHtmlSnippet;
use Team64j\LaravelManagerApi\Components\ActionsButtons;
use Team64j\LaravelManagerApi\Components\Panel;
use Team64j\LaravelManagerApi\Components\Tabs;
use Team64j\LaravelManagerApi\Components\Title;

class ChunkLayout extends Layout
{
    /**
     * @param SiteHtmlSnippet|null $model
     *
     * @return array
     */
    public function default(SiteHtmlSnippet $model = null): array
    {
        return [
            ActionsButtons::make()
                ->setCancel()
                ->setSaveAnd()
                ->if(
                    $model->getKey(),
                    fn(ActionsButtons $actions) => $actions->setDelete()->setCopy()
                ),

            Title::make()
                ->setModel('name')
                ->setTitle(Lang::get('global.new_htmlsnippet'))
                ->setIcon('fa fa-th-large')
                ->setId($model->getKey()),
        ];
    }

    /**
     * @param SiteHtmlSnippet|null $model
     *
     * @return array
     */
    public function titleDefault(SiteHtmlSnippet $model = null): array
    {
        return [
            'title' => $model->name ?: Lang::get('global.new_htmlsnippet'),
            'icon' => 'fa fa-th-large',
        ];
    }

    public function list(): array
    {
        return [
            ActionsButtons::make()
                ->setNew(
                    Lang::get('global.new_htmlsnippet'),
                    'Chunk',
                    'btn-green',
                    'fa fa-plus'
                ),

            Title::make()
                ->setTitle(Lang::get('global.htmlsnippets'))
                ->setIcon('fa fa-th-large')
                ->setHelp(Lang::get('global.htmlsnippet_management_msg')),

            Tabs::make()
                ->setId('elements')
                ->setHistory('element')
                ->addTab('templates', Lang::get('global.templates'), 'fa fa-newspaper', 'py-4', ['edit_template'])
                ->addTab(
                    'tvs',
                    Lang::get('global.tmplvars'),
                    'fa fa-th-large',
                    'py-4',
                    ['edit_template', 'edit_snippet', 'edit_chunk', 'edit_plugin']
                )
                ->addTab('chunks', Lang::get('global.htmlsnippets'), 'fa fa-th-large', 'py-4', ['edit_chunk'])
                ->addTab('snippets', Lang::get('global.snippets'), 'fa fa-code', 'py-4', ['edit_snippet'])
                ->addTab('plugins', Lang::get('global.plugins'), 'fa fa-plug', 'py-4', ['edit_plugin'])
                ->addTab('modules', Lang::get('global.modules'), 'fa fa-cubes', 'py-4', ['edit_module'])
                ->addTab(
                    'categories',
                    Lang::get('global.category_management'),
                    'fa fa-object-group',
                    'py-4',
                    ['category_manager']
                )
                ->addSlot(
                    'chunks',
                    Panel::make()
                        ->setId('chunks')
                        ->setModel('data')
                        ->setData([])
                        ->setRoute('Chunk')
                        ->setHistory(true)
                        ->addColumn(
                            ['#', 'locked'],
                            null,
                            ['width' => '3rem'],
                            false,
                            [
                                '<i class="fa fa-th-large fa-fw"/>',
                                '<i class="fa fa-th-large fa-fw" title="' .
                                Lang::get('global.locked') . '"><i class="fa fa-lock"/></i>',
                            ]
                        )
                        ->addColumn(
                            'id',
                            Lang::get('global.id'),
                            ['width' => '5rem', 'textAlign' => 'right', 'fontWeight' => 'bold'],
                            true
                        )
                        ->addColumn(
                            'name',
                            Lang::get('global.htmlsnippet_name'),
                            ['width' => '20rem', 'fontWeight' => 500],
                            true
                        )
                        ->addColumn('description', Lang::get('global.htmlsnippet_desc'))
                        //->addColumn('category', Lang::get('global.category_heading'), ['width' => '15rem'], true)
                        ->addColumn(
                            'locked',
                            Lang::get('global.locked'),
                            ['width' => '10rem', 'textAlign' => 'center'],
                            true,
                            [
                                0 => '<span class="text-green-600">' . Lang::get('global.no') . '</span>',
                                1 => '<span class="text-rose-600">' . Lang::get('global.yes') . '</span>',
                            ]
                        )
                        ->addColumn(
                            'disabled',
                            Lang::get('global.disabled'),
                            ['width' => '10rem', 'textAlign' => 'center'],
                            true,
                            [
                                0 => '<span class="text-green-600">' . Lang::get('global.no') . '</span>',
                                1 => '<span class="text-rose-600">' . Lang::get('global.yes') . '</span>',
                            ]
                        )
                        ->addColumn(
                            'actions',
                            Lang::get('global.onlineusers_action'),
                            ['width' => '10rem', 'textAlign' => 'center'],
                            false,
                            [],
                            [
                                'copy' => [
                                    'icon' => 'far fa-clone fa-fw hover:text-blue-500',
                                    'help' => Lang::get('global.duplicate'),
                                    'helpFit' => true,
                                    'noOpacity' => true,
                                ],
                                'delete' => [
                                    'icon' => 'fa fa-trash fa-fw hover:text-rose-600',
                                    'help' => Lang::get('global.delete'),
                                    'helpFit' => true,
                                    'noOpacity' => true,
                                ],
                            ]
                        ),
                    ['edit_chunk']
                ),
        ];
    }

    /**
     * @return array
     */
    public function titleList(): array
    {
        return [
            'title' => Lang::get('global.htmlsnippets'),
            'icon' => 'fa fa-th-large',
        ];
    }
}
