<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Illuminate\Support\Facades\Lang;
use Team64j\LaravelManagerApi\Components\ActionsButtons;
use Team64j\LaravelManagerApi\Components\CodeEditor;
use Team64j\LaravelManagerApi\Components\Input;
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
                ->setModel('basename')
                ->setIcon('fa fa-file')
                ->setId($data['size'] ?? null),

            Template::make()
                ->setClass('px-6')
                ->setSlot([
                    Lang::get('global.filemanager_path_title') . ' ' . $data['path'],
                    Input::make('basename', Lang::get('global.files_filename'))->setInputClass('text-xl font-bold px-4 py-2'),
                    CodeEditor::make('content')->setLanguage($data['lang']),
                ]),
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
