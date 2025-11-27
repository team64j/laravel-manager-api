<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Team64j\LaravelManagerApi\Models\SiteContent;
use Team64j\LaravelManagerComponents\GlobalTab;
use Team64j\LaravelManagerComponents\Panel;
use Team64j\LaravelManagerComponents\Tabs;
use Team64j\LaravelManagerComponents\Title;

class ResourcesLayout extends Layout
{
    /**
     * @return string
     */
    public function icon(): string
    {
        return 'fa fa-edit';
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return '';
    }

    /**
     * @param SiteContent|null $model
     *
     * @return array
     */
    public function default(?SiteContent $model = null): array
    {
        $title = $model->pagetitle ?? 'root';

        return [
            GlobalTab::make()
                ->setTitle($title)
                ->setIcon($this->icon()),

            Title::make()
                ->setTitle($title)
                ->setIcon($this->icon()),

            Tabs::make()
                ->setId('resource')
                ->addTab(
                    'general',
                    __('global.settings_general'),
                    slot: [
                        Panel::make('data')
                            ->setId('resources')
                            ->setRoute('/resource/:id')
                            ->setHistory(true)
                            ->addColumn(
                                'id',
                                __('global.id'),
                                ['width' => '4rem', 'textAlign' => 'right'],
                                true
                            )
                            ->addColumn(
                                'isfolder',
                                __('global.folder'),
                                ['width' => '4rem', 'textAlign' => 'right'],
                                true,
                                [
                                    0 => '<i class="far fa-file"></i>',
                                    1 => '<i class="fa fa-folder"</i>',
                                ]
                            )
                            ->addColumn(
                                'pagetitle',
                                __('global.pagetitle'),
                                [],
                                true
                            )
                            ->addColumn(
                                'createdon',
                                __('global.createdon'),
                                ['width' => '12rem', 'textAlign' => 'center'],
                                true
                            )
                            ->addColumn(
                                'publishedon',
                                __('global.publish_date'),
                                ['width' => '12rem', 'textAlign' => 'center'],
                                true
                            )
                            ->addColumn(
                                'published',
                                __('global.page_data_status'),
                                ['width' => '12rem', 'textAlign' => 'right'],
                                true,
                                [
                                    0 => '<span class="text-error">' . __('global.page_data_unpublished') . '</span>',
                                    1 => '<span class="text-success">' . __('global.page_data_published') . '</span>',
                                ]
                            ),
                    ]
                ),
        ];
    }
}
