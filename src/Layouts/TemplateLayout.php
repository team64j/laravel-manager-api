<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Team64j\LaravelEvolution\Models\SiteTemplate;
use Team64j\LaravelManagerApi\Components\ActionsButtons;
use Team64j\LaravelManagerApi\Components\Checkbox;
use Team64j\LaravelManagerApi\Components\CodeEditor;
use Team64j\LaravelManagerApi\Components\Input;
use Team64j\LaravelManagerApi\Components\Panel;
use Team64j\LaravelManagerApi\Components\Select;
use Team64j\LaravelManagerApi\Components\Tabs;
use Team64j\LaravelManagerApi\Components\Template;
use Team64j\LaravelManagerApi\Components\Textarea;
use Team64j\LaravelManagerApi\Components\Title;

class TemplateLayout extends Layout
{
    /**
     * @param SiteTemplate|null $model
     *
     * @return array
     */
    public function default(SiteTemplate $model = null): array
    {
        $data[] = ActionsButtons::make()
            ->setCancel()
            ->setSaveAnd()
            ->if(
                $model->getKey(),
                fn(ActionsButtons $actions) => $actions->setDelete()->setCopy()
            );

        $data[] = Title::make()
            ->setModel('templatename')
            ->setHelp(Lang::get('global.template_msg'))
            ->setId($model->getKey())
            ->setIcon('fa fa-newspaper')
            ->setTitle(Lang::get('global.new_template'));

        $bladeFile = Config::get('view.app') . '/' . $model->templatealias . '.blade.php';
        $isBladeFile = file_exists($bladeFile);
        $relativeBladeFile = str_replace(dirname(base_path()), '', $bladeFile);

        $data[] = Tabs::make()
            ->setId('template')
            ->addTab('default', Lang::get('global.settings_general'), null, 'p-6 flex flex-wrap')
            ->addSlot(
                'default', [
                    Template::make(
                        'flex flex-wrap grow md:basis-2/3 xl:basis-9/12 md:pr-3',
                        [
                            Input::make(
                                'templatename',
                                Lang::get('global.template_name')
                            )
                                ->isRequired()
                                ->setRequired(
                                    Config::get('global.default_template') == $model->id ? trim(
                                        Lang::get('global.defaulttemplate_title'),
                                        ':'
                                    ) : ''
                                ),

                            Input::make(
                                'templatealias',
                                Lang::get('global.alias')
                            ),

                            Textarea::make(
                                'description',
                                Lang::get('global.template_desc')
                            ),
                        ]
                    ),

                    Template::make(
                        'flex flex-wrap grow md:basis-1/3 xl:basis-3/12 md:pl-3',
                        [
                            Select::make(
                                'category',
                                Lang::get('global.existing_category')
                            )
                                ->setUrl('/categories/select')
                                ->addOption(
                                    $model->category,
                                    $model->categories ? $model->categories->category : Lang::get('global.no_category')
                                )
                                ->setNew(''),

                            Checkbox::make('selectable', Lang::get('global.template_selectable'))
                                ->setCheckedValue(1, 0),

                            Checkbox::make('locked', Lang::get('global.lock_template_msg'))
                                ->setCheckedValue(1, 0),
                        ]
                    ),

                    ($isBladeFile
                        ? '<span class="text-green-600">' . Lang::get('global.template_assigned_blade_file') . ': ' .
                        $relativeBladeFile . '</span>'
                        :
                        Checkbox::make('createbladefile', Lang::get('global.template_create_blade_file'))
                            ->setCheckedValue(1, 0)),

                    CodeEditor::make('content', Lang::get('global.template_code'))
                        ->setLanguage('html'),
                ]
            )
            ->addTab('tvs', Lang::get('global.template_assignedtv_tab'), null, 'py-8 flex flex-wrap')
            ->addSlot(
                'tvs',
                [
                    Panel::make()
                        ->setId('tvs')
                        ->setModel('')
                        ->setHistory(true)
                        ->isFilter()
                        ->setSlotTop('<div class="font-bold">' . Lang::get('global.template_tv_msg') . '</div>')
                        ->setUrl('/templates/' . ($model->getKey() ?: 'new') . '/tvs')
                        ->addColumn(
                            'attach',
                            Lang::get('global.role_udperms'),
                            ['width' => '4rem', 'textAlign' => 'center'],
                            true
                        )
                        ->addColumn(
                            'id',
                            'ID',
                            ['width' => '4rem', 'textAlign' => 'right'],
                            true
                        )
                        ->addColumn(
                            'name',
                            Lang::get('global.tmplvars_name'),
                            ['fontWeight' => '500'],
                            true
                        )
                        ->addColumn(
                            'caption',
                            Lang::get('global.tmplvars_caption')
                        )
                        ->addColumn(
                            'rank',
                            Lang::get('global.tmplvars_rank'),
                            ['textAlign' => 'center']
                        ),
                ]
            );

        return $data;
    }

    /**
     * @param SiteTemplate|null $model
     *
     * @return array
     */
    public function titleDefault(SiteTemplate $model = null): array
    {
        return [
            'title' => $model->templatename ?: Lang::get('global.new_template'),
            'icon' => 'fa fa-newspaper',
        ];
    }

    /**
     * @return array
     */
    public function list(): array
    {
        $data[] = ActionsButtons::make()
            ->setNew(
                Lang::get('global.new_template'),
                'Template',
                'btn-green',
                'fa fa-plus'
            );

        $data[] = Title::make()
            ->setTitle(Lang::get('global.templates'))
            ->setIcon('fa fa-newspaper')
            ->setHelp(Lang::get('global.template_management_msg'));

        $data[] = Tabs::make()
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
                'templates',
                Panel::make()
                    ->setId('templates')
                    ->setModel('data')
                    ->setData([])
                    ->setRoute('Template')
                    ->setHistory(true)
                    ->addColumn('#', null, ['width' => '3rem'])
                    ->addColumn(
                        'id',
                        Lang::get('global.id'),
                        ['width' => '5rem', 'textAlign' => 'right', 'fontWeight' => 'bold'],
                        true
                    )
                    ->addColumn(
                        'templatename',
                        Lang::get('global.template_name'),
                        ['width' => '20rem', 'fontWeight' => 500],
                        true
                    )
                    ->addColumn(
                        'file',
                        Lang::get('global.files_management'),
                        ['width' => '5rem', 'textAlign' => 'center']
                    )
                    ->addColumn('description', Lang::get('global.template_desc'))
                    ->addColumn('category', Lang::get('global.category_heading'), ['width' => '15rem'], true)
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
                ['edit_template']
            );

        return $data;
    }

    /**
     * @return array
     */
    public function titleList(): array
    {
        return [
            'title' => Lang::get('global.templates'),
            'icon' => 'fa fa-newspaper',
        ];
    }
}
