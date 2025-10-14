<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Team64j\LaravelManagerComponents\Actions;
use Team64j\LaravelManagerComponents\CodeEditor;
use Team64j\LaravelManagerComponents\Crumbs;
use Team64j\LaravelManagerComponents\GlobalTab;
use Team64j\LaravelManagerComponents\Media;
use Team64j\LaravelManagerComponents\Tabs;
use Team64j\LaravelManagerComponents\Title;

class FileLayout extends Layout
{
    /**
     * @return string
     */
    public function title(): string
    {
        return __('global.new_file');
    }

    /**
     * @param string|null $type
     *
     * @return string
     */
    public function icon(?string $type = null): string
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

    /**
     * @param array $data
     *
     * @return array
     */
    public function default(array $data = []): array
    {
        return [
            GlobalTab::make()
                ->setIcon($this->icon($data['ext'] ?? 'default'))
                ->setTitle($data['basename'] ?? $this->title()),

            Actions::make()
                ->setCancel(__('global.close'))
                ->when(
                    $data['basename'] ?? false,
                    fn(Actions $actions) => $actions->setDelete()->setCopy()
                )
                ->setSaveAnd(),

            Title::make('basename')
                ->setIcon($this->icon($data['ext'] ?? 'default'))
                ->setId($data['size'] ?? null),

            Tabs::make()
                ->addTab(
                    'default',
                    slot: [
                        stripos($data['type'], 'image/') !== false ? Media::make('path')->setData($data) : null,
                        isset($data['content']) ? CodeEditor::make('content')
                            ->setLanguage($data['lang'])
                            ->setRows('auto')
                            ->setInputClass('h-full') : null,
                    ],
                ),

            Crumbs::make()
                ->setData(
                    array_map(
                        fn($i) => [
                            'name' => $i,
                        ],
                        explode('\\', $data['path'])
                    )
                ),
        ];
    }
}
