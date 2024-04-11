<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use EvolutionCMS\Models\SiteContent;
use Illuminate\Support\Facades\Lang;
use Team64j\LaravelManagerApi\Components\Panel;
use Team64j\LaravelManagerApi\Components\Title;

class DocumentsLayout extends Layout
{
    /**
     * @param SiteContent|null $model
     *
     * @return array
     */
    public function default(SiteContent $model = null): array
    {
        return [
            Title::make()
                ->setTitle($model->pagetitle)
                ->setIcon('fa fa-edit'),

            Panel::make()
                ->setModel('data')
                ->setId('documents')
                ->setRoute('/document/:id')
                ->addColumn('id', Lang::get('global.id'), ['width' => '4rem', 'textAlign' => 'right'], true)
                ->addColumn('isfolder', Lang::get('global.folder'), ['width' => '4rem', 'textAlign' => 'right'], true, [
                    0 => '<i class="far fa-file"></i>',
                    1 => '<i class="fa fa-folder"</i>',
                ])
                ->addColumn('pagetitle', Lang::get('global.pagetitle'), [], true)
                ->addColumn(
                    'createdon',
                    Lang::get('global.createdon'),
                    ['width' => '12rem', 'textAlign' => 'center'],
                    true
                )
                ->addColumn(
                    'publishedon',
                    Lang::get('global.publish_date'),
                    ['width' => '12rem', 'textAlign' => 'center'],
                    true
                )
                ->addColumn(
                    'published',
                    Lang::get('global.page_data_status'),
                    ['width' => '12rem', 'textAlign' => 'right'],
                    true,
                    [
                        0 => '<span class="text-rose-600">' . Lang::get('global.page_data_unpublished') . '</span>',
                        1 => '<span class="text-green-600">' . Lang::get('global.page_data_published') . '</span>',
                    ]
                ),
        ];
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return 'fa fa-edit';
    }
}
