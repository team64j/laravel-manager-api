<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\URL;
use OpenApi\Annotations as OA;
use Team64j\LaravelManagerApi\Http\Requests\FilesRequest;
use Team64j\LaravelManagerApi\Http\Resources\FilesResource;
use Team64j\LaravelManagerApi\Layouts\FilesLayout;

class FilesController extends Controller
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
     * @param FilesLayout $layout
     *
     * @return AnonymousResourceCollection
     */
    public function index(FilesRequest $request, FilesLayout $layout): AnonymousResourceCollection
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
     * @param FilesLayout $layout
     *
     * @return AnonymousResourceCollection
     */
    public function show(FilesRequest $request, string $files, FilesLayout $layout): AnonymousResourceCollection
    {
        $data = [];
        $root = Config::get('global.rb_base_dir', App::basePath());
        $parent = trim(base64_decode($files), './');
        $parentPath = $root . ($parent ? DIRECTORY_SEPARATOR . $parent : '');
        $opened = $request->has('opened') ? $request->string('opened')
            ->explode(',')
            ->map(fn($i) => $i)
            ->toArray() : [];

        if (file_exists($parentPath)) {
            $directories = File::directories($parentPath);
            $files = File::files($parentPath, true);

            //if ($root != $parentPath) {
                $data[] = [
                    'key' => 'back',
                    'title' => '...',
                    'icon' => '<i class="fa fa-arrow-left fa-fw"></i>',
                    'folder' => false,
                ];
            //}

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
                    'folder' => true,
                    'size' => $this->getSize(File::size($directory)),
                    'date' => $this->getDate(filemtime($directory)),
                ];

                if (in_array($key, $opened)) {
                    $newRequest = clone $request;
                    $newRequest->query->set('after', null);
                    $newRequest->query->set('parent', $key);
                    $item['data'] = $this->show($newRequest, '')['data'] ?? [];
                }

                $data[] = $item;
            }

            foreach ($files as $file) {
                $title = $file->getFilename();
                $key = base64_encode(
                    str_replace(
                        DIRECTORY_SEPARATOR,
                        '/',
                        trim(str_replace($root, '', $file->getPathname()), DIRECTORY_SEPARATOR)
                    )
                );

                $type = $file->getExtension();

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
        }

        return FilesResource::collection($data)
            ->additional([
                'layout' => $layout->default(),
                'meta' => [
                    'title' => Lang::get('global.files_management'),
                    'icon' => $layout->getIcon(),
                    'category' => true,
                    'columns' => [
                        [
                            'name' => 'icon',
                            'label' => Lang::get('global.icon'),
                            'width' => '2rem',
                            'style' => [
                                'textAlign' => 'center',
                            ],
                            'values' => [
                                'folder' => [
                                    'false' => '<i class="far fa-file fa-fw"></i>',
                                    'true' => '<i class="far fa-folder fa-fw"></i>',
                                ]
                            ]
                        ],
                        [
                            'name' => 'title',
                            'label' => Lang::get('global.files_filename'),
                        ],
                        [
                            'name' => 'size',
                            'label' => Lang::get('global.files_filesize'),
                            'width' => '12rem',
                            'style' => [
                                'textAlign' => 'right',
                            ],
                        ],
                        [
                            'name' => 'date',
                            'label' => Lang::get('global.datechanged'),
                            'width' => '12rem',
                            'style' => [
                                'textAlign' => 'right',
                                'whiteSpace' => 'nowrap',
                            ],
                        ],
                    ],
                ],
            ]);
    }

    /**
     * @OA\Get(
     *     path="/files/tree",
     *     summary="Получение списка файлов для древовидного меню",
     *     tags={"File"},
     *     security={{"Api":{}}},
     *     parameters={
     *         @OA\Parameter (name="parent", in="query", @OA\Schema(type="string")),
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
     *
     * @return AnonymousResourceCollection
     */
    public function tree(FilesRequest $request): AnonymousResourceCollection
    {
        $data = [];
        $root = Config::get('global.rb_base_dir', App::basePath());
        $parent = trim(base64_decode($request->input('parent', '')), './');
        $parentPath = $root . DIRECTORY_SEPARATOR . $parent;
        $opened = $request->has('opened') ? $request->string('opened')
            ->explode(',')
            ->map(fn($i) => $i)
            ->toArray() : [];

        if (file_exists($parentPath)) {
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
                    'folder' => true,
                    'category' => true,
                ];

                if (in_array($key, $opened, true)) {
                    $newRequest = clone $request;
                    $newRequest->query->set('after', null);
                    $newRequest->query->set('parent', $key);
                    $item['data'] = $this->tree($newRequest)->resource->toArray();
                }

                $item['hideChildren'] = !File::directories($directory);

                $data[] = $item;
            }
        }

        return FilesResource::collection($data)
            ->additional([
                'meta' => [
                    'category' => false,
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
}
