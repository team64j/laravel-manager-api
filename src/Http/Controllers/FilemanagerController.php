<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use OpenApi\Annotations as OA;
use Team64j\LaravelManagerApi\Http\Requests\FilesRequest;
use Team64j\LaravelManagerApi\Http\Resources\JsonResourceCollection;
use Team64j\LaravelManagerApi\Http\Resources\JsonResource;
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
     * @return JsonResourceCollection
     */
    public function index(FilesRequest $request, FilemanagerLayout $layout): JsonResourceCollection
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
     * @return JsonResourceCollection
     */
    public function show(FilesRequest $request, string $files, FilemanagerLayout $layout): JsonResourceCollection
    {
        $data = [];
        $root = realpath(config('global.rb_base_dir', app()->basePath()));
        $parent = trim(base64_decode($files), './');
        $parentPath = realpath($root . ($parent ? DIRECTORY_SEPARATOR . $parent : ''));
        $extensions = array_merge(
//            explode(',', config('global.upload_files', '')),
//            explode(',', config('global.upload_flash', '')),
//            explode(',', config('global.upload_images', '')),
//            explode(',', config('global.upload_media', '')),
        );
        $fullTitle = $layout->title();;

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

                $filePath = str_replace(
                    DIRECTORY_SEPARATOR,
                    '/',
                    trim(str_replace($root, '', $file->getPathname()), DIRECTORY_SEPARATOR)
                );

                if (!$type) {
                    $mimeType = File::mimeType($file->getPathname());

                    $type = match ($mimeType) {
                        'text/plain' => 'txt',
                        default => ''
                    };
                }

                $item = [
                    'key' => base64_encode($filePath),
                    'title' => $file->getFilename(),
                    'value' => '/' . $filePath,
                    'folder' => false,
                    'type' => $type,
                    'unpublished' => !$file->isWritable() || !$file->isReadable(),
                    'class' => 'f-ext-' . $file->getExtension(),
                    'size' => $this->getSize($file->getSize()),
                    'date' => $this->getDate($file->getATime()),
                    'dbClick' => 'modal:select'
                ];

                $mimeType = File::mimeType($file->getPathname());
                $isImage = str_starts_with($mimeType, 'image/') || $file->getExtension() == 'svg';

                if ($isImage) {
                    $folderBase = str_replace(
                        realpath(app()->basePath()),
                        '',
                        realpath(config('global.rb_base_dir'))
                    );

                    $imageUrl = str_replace(
                        DIRECTORY_SEPARATOR,
                        '/',
                        $folderBase . str_replace($root, '', $file->getPathname())
                    );

                    $item['icon'] = '<img src="' . url($imageUrl) . '" class="inline-block" />';
                }

                $data[] = $item;
            }
        }

        return JsonResource::collection($data)
            ->layout($layout->default())
            ->meta([
                'title' => $layout->title(),
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
     * @return JsonResourceCollection
     */
    public function tree(FilesRequest $request): JsonResourceCollection
    {
        $data = [];
        $settings = $request->collect('settings')->toArray();
        $root = realpath(config('global.rb_base_dir', app()->basePath()));
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

        return JsonResource::collection($data);
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
