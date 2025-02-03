<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Team64j\LaravelEvolution\Traits\SoftDeletes;
use Team64j\LaravelEvolution\Traits\TimeMutatorTrait;

/**
 * @property int $id
 * @property int $parent
 * @property int $alias_visible
 * @property int $isfolder
 * @property int $template
 * @property int $deleted
 * @property int $richtext
 * @property int $hidemenu
 * @property int $published
 * @property int $hide_from_tree
 * @property string $alias
 * @property string $pagetitle
 * @property string $type
 * @property string $contentType
 * @property string $content_dispo
 * @property SiteTemplate $tpl
 * @property SiteContent $parents
 * @property SiteTmplvarContentvalue[]|Collection $templateValues
 * @property DocumentgroupName[]|Collection $documentGroups
 * @method static |self|Builder withoutProtected()
 */
class SiteContent extends Model
{
    use TimeMutatorTrait;
    use SoftDeletes;

    public const CREATED_AT = 'createdon';
    public const UPDATED_AT = 'editedon';
    const DELETED_AT = 'deletedon';
    const DELETED = 'deleted';

    /**
     * @var string
     */
    protected $table = 'site_content';

    /**
     * @var string
     */
    protected $dateFormat = 'U';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->setRawAttributes([
            'published' => Config::get('global.publish_default'),
            'template' => Config::get('global.default_template'),
            'hide_from_tree' => 0,
            'alias_visible' => 1,
            'richtext' => 1,
            'menuindex' => 0,
            'searchable' => Config::get('global.search_default'),
            'cacheable' => Config::get('global.cache_default'),
            'type' => 'document',
            'contentType' => 'text/html',
            'parent' => 0,
            'content_dispo' => 0,
        ], true);

        parent::__construct($attributes);
    }

    /**
     * @var array|string[]
     */
    protected $casts = [
        'published' => 'int',
        'pub_date' => 'int',
        'unpub_date' => 'int',
        'parent' => 'int',
        'isfolder' => 'int',
        'richtext' => 'int',
        'template' => 'int',
        'menuindex' => 'int',
        'searchable' => 'int',
        'cacheable' => 'int',
        'createdby' => 'int',
        'createdon' => 'datetime',
        'editedby' => 'int',
        'editedon' => 'datetime',
        'deleted' => 'int',
        'deletedby' => 'int',
        'publishedon' => 'int',
        'publishedby' => 'int',
        'hide_from_tree' => 'int',
        'privateweb' => 'int',
        'privatemgr' => 'int',
        'content_dispo' => 'int',
        'hidemenu' => 'int',
        'alias_visible' => 'int',
    ];

    /**
     * @var string[]
     */
    protected $fillable = [
        'type',
        'contentType',
        'pagetitle',
        'longtitle',
        'description',
        'alias',
        'link_attributes',
        'published',
        'pub_date',
        'unpub_date',
        'parent',
        'isfolder',
        'introtext',
        'content',
        'richtext',
        'template',
        'menuindex',
        'searchable',
        'cacheable',
        'createdby',
        'editedby',
        'deleted',
        'deletedby',
        'publishedon',
        'publishedby',
        'menutitle',
        'hide_from_tree',
        'privateweb',
        'privatemgr',
        'content_dispo',
        'hidemenu',
        'alias_visible',
    ];

//    /**
//     * @param $value
//     *
//     * @return string
//     */
//    public function setDescriptionAttribute($value): string
//    {
//        return (string) $value;
//    }
//
//    /**
//     * @param $value
//     *
//     * @return string
//     */
//    public function setIntrotextAttribute($value): string
//    {
//        return (string) $value;
//    }
//
//    /**
//     * @param $value
//     *
//     * @return string
//     */
//    public function setMenutitleAttribute($value): string
//    {
//        return (string) $value;
//    }
//
//    /**
//     * @param $value
//     *
//     * @return string
//     */
//    public function setLongtitleAttribute($value): string
//    {
//        return (string) $value;
//    }
//
//    /**
//     * @param $value
//     *
//     * @return string
//     */
//    public function setLinkAttributesAttribute($value): string
//    {
//        return (string) $value;
//    }
//
//    /**
//     * @param $value
//     *
//     * @return string
//     */
//    public function setCreatedonAttribute($value): string
//    {
//        return (string) $value;
//    }
//
//    /**
//     * @param $value
//     *
//     * @return string
//     */
//    public function setEditedonAttribute($value): string
//    {
//        return (string) $value;
//    }
//
//    /**
//     * @param $value
//     *
//     * @return string
//     */
//    public function setPublishedonAttribute($value): string
//    {
//        return (string) $value;
//    }

    /**
     * @param $value
     *
     * @return string
     */
    public function getCreatedonAttribute($value): string
    {
        return $this->convertDateTime($value);
    }

    /**
     * @param $value
     *
     * @return string
     */
    public function getEditedonAttribute($value): string
    {
        return $this->convertDateTime($value);
    }

    /**
     * @param $value
     *
     * @return string
     */
    public function getPubDateAttribute($value): string
    {
        return $this->serializeDate(Carbon::parse($value));
    }

    /**
     * @param $value
     *
     * @return string
     */
    public function getUnpubDateAttribute($value): string
    {
        return $this->serializeDate(Carbon::parse($value));
    }

    /**
     * @param $value
     *
     * @return string
     */
    public function getPublishedonAttribute($value): string
    {
        return $this->serializeDate(Carbon::parse($value));
    }

    /**
     * @param array $attributes
     * @param array $options
     *
     * @return bool
     */
    public function update(array $attributes = [], array $options = []): bool
    {
        if (isset($attributes['pub_date']) && is_string($attributes['pub_date'])) {
            $attributes['pub_date'] = Carbon::parse($attributes['pub_date'])->timestamp;
        }

        if (isset($attributes['unpub_date']) && is_string($attributes['unpub_date'])) {
            $attributes['unpub_date'] = Carbon::parse($attributes['unpub_date'])->timestamp;
        }

        if (isset($attributes['publishedon']) && is_string($attributes['publishedon'])) {
            $attributes['publishedon'] = Carbon::parse($attributes['publishedon'])->timestamp;
        }

        return parent::update($attributes, $options);
    }

    /**
     * @return Collection
     */
    public function getTvs(): Collection
    {
        /** @var Collection $docTv */
        if ($this->tpl->tvs === null) {
            return Collection::make();
        }

        $docTv = $this->templateValues->pluck('value', 'tmplvarid');

        return $this->tpl->tvs()->with('category')->get()->map(function (SiteTmplvar $tmplvar) use ($docTv) {
            $value = $docTv->has($tmplvar->getKey()) ? $docTv->get($tmplvar->getKey()) : $tmplvar->default_text;

            switch ($tmplvar->type) {
                case 'radio':
                case 'option':
                    break;

                case 'checkbox':
                case 'listbox-multiple':
                    if (!is_array($value)) {
                        $value = $value == '' ? [] : explode('||', $value);
                    }

                    break;

                default:
            }

            $category = $tmplvar->getRelation('category');

            return array_merge(
                $tmplvar->withoutRelations()->toArray(),
                [
                    'value' => $value,
                    'category_name' => $category->category ?? Lang::get('global.no_category'),
                    'pivot_rank' => $tmplvar->pivot->rank,
                ]
            );
        });
    }

    /**
     * @return BelongsTo
     */
    public function tpl(): BelongsTo
    {
        return $this->belongsTo(SiteTemplate::class, 'template', 'id')->withDefault();
    }

    /**
     * @return HasMany
     */
    public function templateValues(): HasMany
    {
        return $this->hasMany(SiteTmplvarContentvalue::class, 'contentid', 'id');
    }

    /**
     * @return BelongsToMany
     */
    public function documentGroups(): BelongsToMany
    {
        return $this->belongsToMany(DocumentgroupName::class, 'document_groups', 'document', 'document_group');
    }

    /**
     * @return Builder|BelongsTo
     */
    public function parents(): BelongsTo|Builder
    {
        return $this->belongsTo(SiteContent::class, 'parent', 'id')->with('parents');
    }

    /**
     * @return HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(SiteContent::class, 'parent', 'id')->with('children');
    }

    public function scopeWithoutProtected($query)
    {
        $query->leftJoin('document_groups', 'document_groups.document', '=', 'site_content.id');
        $query->where(function ($query) {
            $docgrp = evo()->getUserDocGroups();
            if (evo()->isFrontend()) {
                $query->where('privateweb', 0);
            } else {
                $query->whereRaw('1 = ' . ($_SESSION['mgrRole'] ?? 0));
                $query->orWhere('site_content.privatemgr', 0);
            }
            if ($docgrp) {
                $query->orWhereIn('document_groups.document_group', $docgrp);
            }
        });

        return $query;
    }

    /**
     * Get the name of the "deleted" column.
     *
     * @return string
     */
    public function getDeletedColumn(): string
    {
        return defined('static::DELETED') ? static::DELETED : 'deleted';
    }

    /**
     * Get the fully qualified "deleted" column.
     *
     * @return string
     */
    public function getQualifiedDeletedColumn(): string
    {
        return $this->qualifyColumn($this->getDeletedColumn());
    }

    /**
     * @param $query
     * @param array $tvList
     * @param string $sep
     * @param bool $tree
     *
     * @return mixed
     */
    public function scopeWithTVs($query, array $tvList = [], string $sep = ':', bool $tree = false)
    {
        $main_table = 'site_content';

        if ($tree) {
            $main_table = 't2';
        }

        if (!empty($tvList)) {
            $query->addSelect($main_table . '.*');
            $tvList = array_unique($tvList);
            $tvListWithDefaults = [];
            foreach ($tvList as $v) {
                $tmp = explode($sep, $v, 2);
                $tvListWithDefaults[$tmp[0]] = !empty($tmp[1]) ? trim($tmp[1]) : '';
            }
            $tvs = SiteTmplvar::whereIn('name', array_keys($tvListWithDefaults))->get()->pluck('id', 'name')->toArray();
            foreach ($tvs as $tvname => $tvid) {
                $query = $query->leftJoin(
                    'site_tmplvar_contentvalues as tv_' . $tvname,
                    function ($join) use ($main_table, $tvid, $tvname) {
                        $join->on($main_table . '.id', '=', 'tv_' . $tvname . '.contentid')->where(
                            'tv_' . $tvname . '.tmplvarid',
                            '=',
                            $tvid
                        );
                    }
                );
                $query = $query->addSelect('tv_' . $tvname . '.value as ' . $tvname);
                $query = $query->groupBy('tv_' . $tvname . '.value');
                if (!empty($tvListWithDefaults[$tvname]) && $tvListWithDefaults[$tvname] == 'd') {
                    $query = $query->leftJoin('site_tmplvars as tvd_' . $tvname, function ($join) use ($tvid, $tvname) {
                        $join->where('tvd_' . $tvname . '.id', '=', $tvid);
                    });
                }
            }
            $query->groupBy($main_table . '.id');
        }

        return $query;
    }
}
