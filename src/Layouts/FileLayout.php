<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Illuminate\Support\Facades\Lang;
use Team64j\LaravelManagerApi\Components\ActionsButtons;
use Team64j\LaravelManagerApi\Components\CodeEditor;
use Team64j\LaravelManagerApi\Components\Media;
use Team64j\LaravelManagerApi\Components\Template;
use Team64j\LaravelManagerApi\Components\Title;

class FileLayout extends Layout
{
    public function default(array $data = []): array
    {
        return [
            ActionsButtons::make()
                ->setCancel()
                ->setSaveAnd()
                ->if(
                    $data['basename'] ?? false,
                    fn(ActionsButtons $actions) => $actions->setDelete()->setCopy()
                ),

            Title::make()
                ->setModel('path')
                ->setIcon('fa fa-file')
                ->setId($data['size'] ?? null),

            Template::make()
                ->setClass('px-6 pb-4')
                ->setSlot(
                    match (true) {
                        (stripos($data['type'], 'text/') !== false ||
                            stripos($data['type'], 'application/json') !== false) => CodeEditor::make('content')
                            ->setLanguage($data['lang'])
                            ->setRows('auto')
                            ->setClass('!m-0'),
                        stripos($data['type'], 'image/') !== false => Media::make('path')
                            ->setData($data),
                        default => [],
                    }
                ),
        ];
    }

    /**
     * @param string|null $filename
     *
     * @return array
     */
    public function titleDefault(string $filename = null): array
    {
        return [
            'title' => $filename ?: Lang::get('global.new_file'),
            'icon' => 'fa fa-cube',
        ];
    }
}
