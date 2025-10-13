<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Models;

use Illuminate\Database\Eloquent\Model;

class SitePluginEvent extends Model
{
    protected $table = 'site_plugin_events';

    public $incrementing = false;

    public $timestamps = false;

    protected $casts = [
        'pluginid' => 'int',
        'evtid'    => 'int',
        'priority' => 'int',
    ];

    protected $fillable = [
        'pluginid',
        'evtid',
        'priority',
    ];
}
