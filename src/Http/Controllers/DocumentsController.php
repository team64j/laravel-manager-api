<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use Illuminate\Support\Facades\Config;
use Team64j\LaravelEvolution\Models\SiteContent;
use Team64j\LaravelManagerApi\Http\Requests\DocumentsRequest;
use Team64j\LaravelManagerApi\Http\Resources\DocumentsResource;
use Team64j\LaravelManagerApi\Layouts\DocumentsLayout;
use Team64j\LaravelManagerApi\Traits\PaginationTrait;

class DocumentsController extends Controller
{
    use PaginationTrait;

    /**
     * @param DocumentsRequest $request
     * @param string $documents
     * @param DocumentsLayout $layout
     *
     * @return DocumentsResource
     */
    public function show(DocumentsRequest $request, string $documents, DocumentsLayout $layout): DocumentsResource
    {
        $order = $request->input('order', 'id');
        $dir = $request->input('dir', 'asc');

        $fields = [
            'id',
            'parent',
            'isfolder',
            'pagetitle',
            'longtitle',
            'menutitle',
            'description',
            'menuindex',
            'hidemenu',
            'hide_from_tree',
            'type',
            'published',
            'deleted',
            'editedon',
            'createdon',
            'publishedon',
        ];

        if (!in_array($order, $fields)) {
            $order = 'id';
        }

        if (!in_array($dir, ['asc', 'desc'])) {
            $dir = 'asc';
        }

        $result = SiteContent::query()
            ->select($fields)
            ->where('parent', $documents)
            ->orderBy($order, $dir)
            ->paginate(Config::get('global.number_of_results'))
            ->appends($request->all());

        $document = SiteContent::query()->find($documents, [
            'id',
            'pagetitle',
        ]);

        return DocumentsResource::make([
            'data' => [
                'data' => $result->items(),
                'pagination' => $this->pagination($result),
                'sorting' => [
                    'order' => $order,
                    'dir' => $dir,
                ],
            ],
        ])
            ->additional([
                'layout' => $layout->default($document),
                'meta' => [
                    'tab' => $layout->titleDefault($document),
                ],
            ]);
    }
}
