<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use OpenApi\Annotations as OA;
use Team64j\LaravelManagerApi\Http\Requests\FileRequest;
use Team64j\LaravelManagerApi\Http\Resources\JsonResourceCollection;
use Team64j\LaravelManagerApi\Http\Resources\JsonResource;
use Team64j\LaravelManagerApi\Layouts\FileLayout;

class FileController extends Controller
{
    /**
     * @OA\Get(
     *     path="/file/{file}",
     *     summary="Получение файла по адресу на сервере",
     *     tags={"File"},
     *     security={{"Api":{}}},
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param FileRequest $request
     * @param string $file
     * @param FileLayout $layout
     *
     * @return JsonResource
     */
    public function show(FileRequest $request, string $file, FileLayout $layout): JsonResource
    {
        $data = [];
        $root = realpath(config('global.filemanager_path', app()->basePath()));
        $filename = trim(base64_decode(urldecode($file)), '/');
        $path = $root . DIRECTORY_SEPARATOR . $filename;
        $types = [
            'text/plain',
            'image/svg+xml',
            'application/json',
            //'application/octet-stream',
        ];

        $extensions = [
            'html',
        ];

        $ignoreExtensions = [
            'woff',
            'woff2',
        ];

        if (File::isFile($path)) {
            $data['path'] = $filename;
            $data['name'] = File::name($path);
            $data['basename'] = File::basename($path);
            $data['type'] = File::mimeType($path);
            $data['ext'] = File::extension($path);
            $data['lang'] = $this->getLang($data['ext'], $data['type']);
            $data['size'] = $this->getSize(File::size($path));
            $data['url'] = str_replace(
                DIRECTORY_SEPARATOR,
                '/',
                url($filename, [], config('global.server_protocol') == 'https')
            );

            $content = File::get($path);

            if (str_starts_with($content, '#!/usr/bin/env php')) {
                $data['lang'] = 'php';
            }

            if (!in_array($data['ext'], $ignoreExtensions, true)) {
                if (
                    in_array($data['type'], $types) ||
                    str($data['type'])->startsWith('text/') ||
                    in_array($data['ext'], $extensions)
                ) {
                    $data['content'] = $content;
                }
            }
        }

        return JsonResource::make($data)
            ->layout($layout->default($data));
    }

    /**
     * @OA\Get(
     *     path="/file/tree",
     *     summary="Получение списка файлов с пагинацией для древовидного меню",
     *     tags={"File"},
     *     security={{"Api":{}}},
     *     parameters={
     *         @OA\Parameter (name="after", in="query", @OA\Schema(type="string")),
     *         @OA\Parameter (name="opened", in="query", @OA\Schema(type="string")),
     *         @OA\Parameter (name="settings", in="query", @OA\Schema(type="string")),
     *     },
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param FileRequest $request
     *
     * @return JsonResourceCollection
     */
    public function tree(FileRequest $request): JsonResourceCollection
    {
        $data = [];
        $settings = $request->collect('settings')->toArray();
        $root = realpath(config('global.filemanager_path', app()->basePath()));
        $path = $settings['parent'] ?? '';
        $parentPath = $root . DIRECTORY_SEPARATOR . trim(base64_decode($path), './');
        $after = basename(base64_decode($settings['after'] ?? ''));
        $show = $settings['show'] ?? [];

        $directories = File::directories($parentPath);
        $files = File::files($parentPath, true);
        $checkAfter = true;
        $limit = config('global.number_of_results');
        $counter = 0;
        $next = '';

        foreach ($directories as $directory) {
            $title = basename($directory);
            $key = base64_encode(
                trim(
                    str_replace(
                        $root,
                        '',
                        $directory
                    ),
                    DIRECTORY_SEPARATOR
                )
            );

            if ($checkAfter && $after) {
                if ($after == $title) {
                    $after = null;
                    $checkAfter = false;
                }

                continue;
            }

            if ($counter++ > $limit) {
                break;
            }

            $date = $this->getDate(filemtime($directory));

            $item = [
                'id' => $key,
                'title' => $title,
                'category' => true,
                'size' => '',
                'date' => $date,
            ];

            if (in_array('_date', $show)) {
                $item['_date'] = $date;
            }

            if (in_array($key, ($settings['opened'] ?? []), true)) {
                $request->query->set(
                    'settings',
                    [
                        'parent' => $key,
                        'after' => null,
                    ] + $settings
                );

                $result = $this->tree($request);

                $item['data'] = $result->resource ?? [];
                $item['meta'] = $result->additional['meta'] ?? [];
            }

            $data[] = $item;

            $query = $settings;
            $query['after'] = base64_encode($title);

            $next = '/file/tree?' . http_build_query(['settings' => $query]);
        }

        $checkAfter = true;

        foreach ($files as $file) {
            $title = $file->getFilename();
            $key = base64_encode(trim(str_replace($root, '', $file->getPathname()), DIRECTORY_SEPARATOR));

            if ($checkAfter && $after) {
                if ($after == $title) {
                    $after = null;
                    $checkAfter = false;
                }

                continue;
            }

            if ($counter++ > $limit) {
                break;
            }

            $type = $file->getExtension();

            if (!$type) {
                $mimeType = File::mimeType($file->getPathname());

                $type = match ($mimeType) {
                    'text/plain' => 'txt',
                    default => ''
                };
            }

            if ($type == 'example') {
                $type = preg_replace('/^.*\.([^.]+)\.example$/D', '$1', $title);
            }

            $date = $this->getDate($file->getATime());
            $size = $this->getSize($file->getSize());

            $item = [
                'id' => $key,
                'title' => $title,
                'type' => $type,
                'unpublished' => !$file->isWritable() || !$file->isReadable(),
                'class' => 'f-ext-' . $file->getExtension(),
                'date' => $date,
                'size' => $size,
            ];

            if (in_array('_date', $show)) {
                $item['_date'] = $date;
            }

            if (in_array('_size', $show)) {
                $item['_size'] = $size;
            }

            $data[] = $item;

            $query = $settings;
            $query['after'] = base64_encode($title);

            $next = '/file/tree?' . http_build_query(['settings' => $query]);
        }

        if (count($data) <= $limit) {
            $next = null;
        }

        return JsonResource::collection($data)
            ->meta([
                'category' => true,
                'pagination' => [
                    'next' => $next,
                    'lang' => [
                        'prev' => __('global.paging_prev'),
                        'next' => __('global.paging_next'),
                    ],
                ],
            ]);
    }

    /**
     * @param int $size
     *
     * @return string
     */
    protected function getSize(int $size = 0): string
    {
        $mb = 1000 * 1024;

        if ($size > $mb) {
            return round($size / $mb, 2) . ' MB';
        }

        return round($size / 1000, 2) . ' KB';
    }

    /**
     * @param int $date
     *
     * @return string
     */
    protected function getDate(int $date): string
    {
        return Carbon::createFromFormat('U', (string) $date)->format('d-m-Y H:i:s');
    }

    /**
     * @param string $ext
     * @param string $type
     *
     * @return string
     */
    protected function getLang(string $ext, string $type = ''): string
    {
        $lang = '';

        switch ($ext) {
            case 'css':
            case 'less':
            case 'cass':
                $lang = 'css';
                break;

            case 'vue':
                $lang = 'vue';
                break;

            case 'js':
            case 'json':
                $lang = 'javascript';
                break;

            case 'xml':
            case 'svg':
                $lang = 'xml';
                break;

            case 'php':
                $lang = 'php';
                break;

            case 'htm':
            case 'html':
            case 'phtml':
                $lang = 'html';
                break;

            case 'md':
                $lang = 'markdown';
                break;

            case 'sql':
                $lang = 'sql';
                break;
        }

        if (!$lang && $type == 'application/json') {
            $lang = 'javascript';
        }

        return $lang;
    }
}
