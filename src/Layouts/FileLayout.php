<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Illuminate\Support\Facades\Lang;
use Team64j\LaravelManagerComponents\Actions;
use Team64j\LaravelManagerComponents\CodeEditor;
use Team64j\LaravelManagerComponents\Media;
use Team64j\LaravelManagerComponents\Template;
use Team64j\LaravelManagerComponents\Title;

class FileLayout extends Layout
{
    public function default(array $data = []): array
    {
        return [
            Actions::make()
                ->setCancel(Lang::get('global.close'))
                ->when(
                    $data['basename'] ?? false,
                    fn(Actions $actions) => $actions->setDelete()->setCopy()
                )
                ->setSaveAnd(),

            Title::make()
                ->setModel('path')
                ->setIcon('fa fa-file')
                ->setId($data['size'] ?? null),

            Template::make()
                ->setClass('px-4 pb-4 grow overflow-hidden')
                ->setSlot([
                    match (true) {
                        (stripos($data['type'], 'text/') !== false ||
                            stripos($data['type'], 'application/json') !== false ||
                            $data['ext'] == 'html') => CodeEditor::make('content')
                            ->setLanguage($data['lang'])
                            ->setRows('auto')
                            ->setClass('!m-0 !p-0 h-full flex'),
                        stripos($data['type'], 'image/') !== false => Media::make('path')
                            ->setData($data),
                        default => [],
                    }
                ]),
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
