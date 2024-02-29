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
                ->when(
                    $data['basename'] ?? false,
                    fn(ActionsButtons $actions) => $actions->setDelete()->setCopy()
                )
                ->setSaveAnd(),

            Title::make()
                ->setModel('path')
                ->setIcon('fa fa-file')
                ->setId($data['size'] ?? null),

            Template::make()
                ->setClass('px-6 pb-4 grow overflow-hidden')
                ->setSlot(
                    match (true) {
                        (stripos($data['type'], 'text/') !== false ||
                            stripos($data['type'], 'application/json') !== false ||
                            in_array($data['ext'], ['html'])) => CodeEditor::make('content')
                            ->setLanguage($data['lang'])
                            ->setRows('auto')
                            ->setClass('!m-0 h-full flex'),
                        stripos($data['type'], 'image/') !== false => Media::make('path')
                            ->setData($data),
                        default => [],
                    }
                ),
        ];
    }

    /**
     * @param string|null $type
     *
     * @return string
     */
    public function getIcon(string $type = null): string
    {
        return match ($type) {
            'default' => 'fa fa-ban',
            'editorconfig', 'htm', 'phtml', 'html', 'txt', 'yml', 'md', 'htaccess', 'env', 'ts', 'js', 'json', 'xml', 'mjs', 'cjs', 'css', 'less', 'cass', 'artisan' => 'fa fa-code',
            'php' => 'fab fa-php',
            'vue' => 'fab fa-vuejs',
            'svg', 'webp', 'jpg', 'jpeg', 'png', 'gif' => 'far fa-image',
            'lock' => 'fa fa-lock',
            'bat' => 'fa fa-file-code',
            'gitignore', 'gitattributes' => 'fab fa-git',
            default => 'far fa-file'
        };
    }
}
