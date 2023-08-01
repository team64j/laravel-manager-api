<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use OpenApi\Annotations as OA;
use Team64j\LaravelManagerApi\Http\Requests\FileRequest;
use Team64j\LaravelManagerApi\Http\Resources\FileResource;
use Team64j\LaravelManagerApi\Layouts\FileLayout;

class FileController extends Controller
{
//    /**
//     * @param FileRequest $request
//     * @param string $file
//     *
//     * @return FileResource
//     */
//    public function index(FileRequest $request, string $file): FileResource
//    {
//        $data = [];
//        $root = realpath(Config::get('global.filemanager_path', App::basePath('../')));
//        $filename = trim(base64_decode(urldecode($file)), '/');
//        $path = $root . DIRECTORY_SEPARATOR . $filename;
//        $types = [
//            'text/plain',
//            'image/svg+xml',
//            'application/json',
//            'application/octet-stream',
//        ];
//
//        $ignoreExtensions = [
//            'woff',
//            'woff2',
//        ];
//
//        if (File::isFile($path)) {
//            $data['path'] = $filename;
//            $data['name'] = File::name($path);
//            $data['basename'] = File::basename($path);
//            $data['type'] = File::mimeType($path);
//            $data['ext'] = File::extension($path);
//            $data['lang'] = '';
//
//            $content = File::get($path);
//
//            if (str_starts_with($content, '#!/usr/bin/env php')) {
//                $data['lang'] = 'php';
//            }
//
//            if (!in_array($data['ext'], $ignoreExtensions, true)) {
//                if (in_array($data['type'], $types) || Str::startsWith($data['type'], 'text/')) {
//                    $data['content'] = $content;
//                }
//            }
//        }
//
//        return FileResource::make($data);
//    }

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
     * @return FileResource
     */
    public function show(FileRequest $request, string $file, FileLayout $layout): FileResource
    {
        $data = [];
        $root = realpath(Config::get('global.filemanager_path', App::basePath()));
        $filename = trim(base64_decode(urldecode($file)), '/');
        $path = $root . DIRECTORY_SEPARATOR . $filename;
        $types = [
            'text/plain',
            'image/svg+xml',
            'application/json',
            'application/octet-stream',
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
                URL::to($filename, [], Config::get('global.server_protocol') == 'https')
            );

            $content = File::get($path);

            if (str_starts_with($content, '#!/usr/bin/env php')) {
                $data['lang'] = 'php';
            }

            if (!in_array($data['ext'], $ignoreExtensions, true)) {
                if (in_array($data['type'], $types) || Str::startsWith($data['type'], 'text/')) {
                    $data['content'] = $content;
                }
            }
        }

        return FileResource::make($data)
            ->additional([
                'layout' => $layout->default($data),
                'meta' => [
                    'tab' => $layout->titleDefault($data['path']),
                ],
            ]);
    }

    /**
     * @OA\Get(
     *     path="/file/tree/{path}",
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
     * @param string $path
     *
     * @return AnonymousResourceCollection
     */
    public function tree(FileRequest $request, string $path): AnonymousResourceCollection
    {
        $data = [];
        $root = realpath(Config::get('global.filemanager_path', App::basePath()));
        $parentPath = $root . DIRECTORY_SEPARATOR . trim(base64_decode($path), './');
        $after = basename(base64_decode($request->string('after', '')->toString()));
        $opened = $request->has('opened') ? $request->string('opened')
            ->explode(',')
            ->map(fn($i) => $i)
            ->toArray() : [];
        $settings = $request->whenFilled('settings', fn($i) => json_decode($i, true));

        $directories = File::directories($parentPath);
        $files = File::files($parentPath, true);
        $checkAfter = true;
        $limit = Config::get('global.number_of_results');
        $counter = -1;
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
                'key' => $key,
                'title' => $title,
                'folder' => true,
                'date' => !empty($settings['show']) && in_array('date', $settings['show']) ? $date : '',
                '_size' => '-',
                '_date' => $date,
            ];

            if (in_array($key, $opened)) {
                $newRequest = clone $request;
                $newRequest->query->set('after', null);
                $item['data'] = $this->tree($newRequest, $key)['data'] ?? [];
            }

            $data[] = $item;

            $next = '/file/tree/' . $path . '?after=' . base64_encode($title);
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

            $size = $this->getSize($file->getSize());
            $date = $this->getDate($file->getATime());

            $item = [
                'key' => $key,
                'title' => $title,
                'type' => $type,
                'unpublished' => !$file->isWritable() || !$file->isReadable(),
                'class' => 'f-ext-' . $file->getExtension(),
                'size' => !empty($settings['show']) && in_array('size', $settings['show']) ? $size : '',
                'date' => !empty($settings['show']) && in_array('date', $settings['show']) ? $date : '',
                '_size' => $size,
                '_date' => $date,
            ];

            $data[] = $item;

            $next = '/file/tree/' . $path . '?after=' . base64_encode($title);
        }

        if (count($data) <= $limit) {
            $next = null;
        }

        return FileResource::collection([
            'data' => [
                'data' => $data,
                'category' => true,
                'pagination' => [
                    'next' => $next,
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
