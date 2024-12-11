<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResourceRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return match ($this->route()->getActionMethod()) {
            'index' => auth()->user()->canAny(['edit_document', 'view_document']),
            'store' => auth()->user()->can('new_document'),
            'update' => auth()->user()->can('save_document'),
            'destroy' => auth()->user()->can('delete_document'),
            default => auth()->user()->canAny(
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
                'attributes.alias' => 'string|required',
                'attributes.alias_visible' => 'int',
                'attributes.cacheable' => 'int',
                'attributes.content' => 'string|nullable',
                'attributes.content_dispo' => 'int',
                'attributes.contentType' => 'string',
                'attributes.createdby' => 'int',
                'attributes.createdon' => 'string|nullable',
                'attributes.deleted' => 'int',
                'attributes.deletedby' => 'int',
                //'attributes.deletedon' => 'string|int',
                'attributes.description' => 'string|nullable',
                'attributes.editedby' => 'int',
                'attributes.editedon' => 'string|nullable',
                'attributes.empty_cache' => 'int',
                'attributes.haskeywords' => 'int',
                'attributes.hasmetatags' => 'int',
                'attributes.hide_from_tree' => 'int',
                'attributes.hidemenu' => 'int',
                'attributes.introtext' => 'string|nullable',
                'attributes.isfolder' => 'int',
                'attributes.link_attributes' => 'string|nullable',
                'attributes.longtitle' => 'string|nullable',
                'attributes.menuindex' => 'int',
                'attributes.menutitle' => 'string|nullable',
                'attributes.pagetitle' => 'string|required',
                'attributes.parent' => 'int|required',
                'attributes.privatemgr' => 'int',
                'attributes.privateweb' => 'int',
                'attributes.pub_date' => 'string|nullable',
                'attributes.published' => 'int',
                'attributes.publishedby' => 'int',
                'attributes.publishedon' => 'string|nullable',
                'attributes.richtext' => 'int',
                'attributes.searchable' => 'int',
                'attributes.template' => 'int',
                'attributes.type' => 'string',
                'attributes.unpub_date' => 'string|nullable',
            ],
            default => []
        };
    }
}
