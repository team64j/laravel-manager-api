<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\URL;
use OpenApi\Annotations as OA;
use Team64j\LaravelManagerApi\Http\Requests\FilesRequest;
use Team64j\LaravelManagerApi\Http\Resources\ApiCollection;
use Team64j\LaravelManagerApi\Http\Resources\ApiResource;
use Team64j\LaravelManagerApi\Layouts\FilemanagerLayout;

class FilemanagerController extends Controller
{
    /**
     * @OA\Get(
     *     path="/files",
     *     summary="Получение списка файлов из корневой директории",
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
     * @param FilesRequest $request
     * @param FilemanagerLayout $layout
     *
     * @return ApiCollection
     */
    public function index(FilesRequest $request, FilemanagerLayout $layout): ApiCollection
    {
        return $this->show($request, '', $layout);
    }

    /**
     * @OA\Get(
     *     path="/files/{files}",
     *     summary="Получение списка файлов из директории",
     *     tags={"File"},
     *     security={{"Api":{}}},
     *     parameters={
     *         @OA\Parameter (name="opened", in="query", @OA\Schema(type="string")),
     *     },
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param FilesRequest $request
     * @param string $files
     * @param FilemanagerLayout $layout
     *
     * @return ApiCollection
     */
    public function show(FilesRequest $request, string $files, FilemanagerLayout $layout): ApiCollection
    {
        $data = [];
        $root = realpath(Config::get('global.rb_base_dir', App::basePath()));
        $parent = trim(base64_decode($files), './');
        $parentPath = realpath($root . ($parent ? DIRECTORY_SEPARATOR . $parent : ''));
        $extensions = array_merge(
//            explode(',', Config::get('global.upload_files', '')),
//            explode(',', Config::get('global.upload_flash', '')),
//            explode(',', Config::get('global.upload_images', '')),
//            explode(',', Config::get('global.upload_media', '')),
        );
        $title = $layout->title();
        $fullTitle = $title;

        if (file_exists((string) $parentPath)) {
            //$directories = File::directories($parentPath);
            $files = File::files($parentPath, true);
            $path = str_replace($root, '', $parentPath);

            if ($path) {
                $data[] = [
                    'key' => base64_encode(trim(dirname($path), DIRECTORY_SEPARATOR)),
                    'folder' => true,
                    'icon' => '<i class="fa fa-arrow-left !text-blue-500"></i>',
                    'route' => [
                        'path' => '/filemanager/:key',
                    ],
                    'contextMenu' => null,
                ];
            }

//            foreach ($directories as $directory) {
//                $title = basename($directory);
//
//                $key = base64_encode(
//                    trim(
//                        str_replace(
//                            $root,
//                            '',
//                            $directory
//                        ),
//                        DIRECTORY_SEPARATOR
//                    )
//                );
//
//                $item = [
//                    'key' => $key,
//                    'title' => $title,
//                    'folder' => true,
//                    'size' => ''/*$this->getSize(File::size($directory))*/,
//                    'date' => $this->getDate(filemtime($directory)),
//                    'route' => [
//                        'path' => '/filemanager/:key',
//                    ],
//                ];
//
//                $data[] = $item;
//            }

            foreach ($files as $file) {
                $type = $file->getExtension();

                if ($extensions && !in_array(strtolower($type), $extensions)) {
                    continue;
                }

                $title = $file->getFilename();
                $key = base64_encode(
                    str_replace(
                        DIRECTORY_SEPARATOR,
                        '/',
                        trim(str_replace($root, '', $file->getPathname()), DIRECTORY_SEPARATOR)
                    )
                );

                if (!$type) {
                    $mimeType = File::mimeType($file->getPathname());

                    $type = match ($mimeType) {
                        'text/plain' => 'txt',
                        default => ''
                    };
                }

                $item = [
                    'key' => $key,
                    'title' => $title,
                    'folder' => false,
                    'type' => $type,
                    'unpublished' => !$file->isWritable() || !$file->isReadable(),
                    'class' => 'f-ext-' . $file->getExtension(),
                    'size' => $this->getSize($file->getSize()),
                    'date' => $this->getDate($file->getATime()),
                ];

                $mimeType = File::mimeType($file->getPathname());
                $isImage = str_starts_with($mimeType, 'image/') || $file->getExtension() == 'svg';

                if ($isImage) {
                    $folderBase = str_replace(
                        realpath(App::basePath()),
                        '',
                        realpath(Config::get('global.rb_base_dir'))
                    );

                    $imageUrl = str_replace(
                        DIRECTORY_SEPARATOR,
                        '/',
                        $folderBase . str_replace($root, '', $file->getPathname())
                    );

                    $item['icon'] = '<img src="' . URL::to($imageUrl) . '" class="inline-block" />';
                }

                $data[] = $item;
            }

            if ($path) {
                $fullTitle .= ':: ' . basename($root) . str_replace(DIRECTORY_SEPARATOR, '/', $path);
            }
        }

        return ApiResource::collection($data)
            ->layout($layout->default())
            ->meta([
                'title' => $title,
                'fullTitle' => $fullTitle,
                'icon' => $layout->icon(),
            ]);
    }

    /**
     * @OA\Get(
     *     path="/files/tree",
     *     summary="Получение списка файлов для древовидного меню",
     *     tags={"File"},
     *     security={{"Api":{}}},
     *     parameters={
     *         @OA\Parameter (name="settings", in="query", @OA\Schema(type="object")),
     *     },
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param FilesRequest $request
     *
     * @return ApiCollection
     */
    public function tree(FilesRequest $request): ApiCollection
    {
        $data = [];
        $settings = $request->collect('settings')->toArray();
        $root = realpath(Config::get('global.rb_base_dir', App::basePath()));
        $path = $settings['parent'] ?? '';
        $parentPath = realpath($root . DIRECTORY_SEPARATOR . trim(base64_decode($path), './'));
        $opened = [];

        if (!empty($settings['opened'])) {
            foreach (array_filter($settings['opened']) as $value) {
                $opened = array_merge($opened, explode(DIRECTORY_SEPARATOR, base64_decode($value)));
            }

            $opened = array_unique($opened);
        }

        if (file_exists((string) $parentPath)) {
            $directories = File::directories($parentPath);

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

                $item = [
                    'key' => $key,
                    'title' => $title,
                    'path' => '',
                    'data' => File::directories($directory) ? [] : null,
                    'folder' => true,
                    'category' => true,
                ];

                if (in_array($title, $opened, true)) {
                    $request->query->set(
                        'settings',
                        [
                            'parent' => $key,
                            'after' => null,
                        ] + $settings
                    );

                    $result = $this->tree($request);

                    $item['data'] = $result->resource ?? [];
                }

                $data[] = $item;
            }
        }

        return ApiResource::collection($data);
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
}
