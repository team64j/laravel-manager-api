<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class DocumentRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return match ($this->route()->getActionMethod()) {
            'index' => Gate::check('edit_document'),
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
        return [
            'alias' => 'string',
            'alias_visible' => 'int',
            'cacheable' => 'int',
            'content' => 'sometimes|string|nullable',
            'content_dispo' => 'int',
            'contentType' => 'string',
            'createdby' => 'int',
            'createdon' => 'string',
            'deleted' => 'int',
            'deletedby' => 'int',
            'deletedon' => 'int',
            'description' => 'string',
            'editedby' => 'int',
            'editedon' => 'string',
            'empty_cache' => 'int',
            'haskeywords' => 'int',
            'hasmetatags' => 'int',
            'hide_from_tree' => 'int',
            'hidemenu' => 'int',
            'introtext' => 'sometimes|string|nullable',
            'isfolder' => 'int',
            //'link_attributes' => 'string',
            'longtitle' => 'string',
            'menuindex' => 'int',
            'menutitle' => 'string',
            'pagetitle' => 'string',
            'parent' => 'int',
            'privatemgr' => 'int',
            'privateweb' => 'int',
            'pub_date' => 'int',
            'published' => 'int',
            'publishedby' => 'int',
            //'publishedon' => 'string',
            'richtext' => 'int',
            'searchable' => 'int',
            'template' => 'int',
            'type' => 'string',
            'unpub_date' => 'int',
        ];
    }
}
