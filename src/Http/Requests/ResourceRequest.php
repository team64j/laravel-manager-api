<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class ResourceRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return match ($this->route()->getActionMethod()) {
            'index' => Gate::any(['edit_document', 'view_document']),
            'store' => Gate::check('new_document'),
            'update' => Gate::check('save_document'),
            'destroy' => Gate::check('delete_document'),
            default => Gate::any(
                [
                    'new_document',
                    'edit_document',
                    'save_document',
                    'delete_document',
                    'view_document',
                    'publish_document',
                ]
            ),
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return match ($this->route()->getActionMethod()) {
            'store', 'update' => [
                'alias' => 'string|required',
                'alias_visible' => 'int',
                'cacheable' => 'int',
                'content' => 'string|nullable',
                'content_dispo' => 'int',
                'contentType' => 'string',
                'createdby' => 'int',
                'createdon' => 'string|nullable',
                'deleted' => 'int',
                'deletedby' => 'int',
                //'deletedon' => 'string|int',
                'description' => 'string|nullable',
                'editedby' => 'int',
                'editedon' => 'string|nullable',
                'empty_cache' => 'int',
                'haskeywords' => 'int',
                'hasmetatags' => 'int',
                'hide_from_tree' => 'int',
                'hidemenu' => 'int',
                'introtext' => 'string|nullable',
                'isfolder' => 'int',
                'link_attributes' => 'string|nullable',
                'longtitle' => 'string|nullable',
                'menuindex' => 'int',
                'menutitle' => 'string|nullable',
                'pagetitle' => 'string|required',
                'parent' => 'int|required',
                'privatemgr' => 'int',
                'privateweb' => 'int',
                'pub_date' => 'string|nullable',
                'published' => 'int',
                'publishedby' => 'int',
                'publishedon' => 'string|nullable',
                'richtext' => 'int',
                'searchable' => 'int',
                'template' => 'int',
                'type' => 'string',
                'unpub_date' => 'string|nullable',
            ],
            default => []
        };
    }
}
