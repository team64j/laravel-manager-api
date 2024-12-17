<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Team64j\LaravelManagerComponents\Actions;
use Team64j\LaravelManagerComponents\Panel;
use Team64j\LaravelManagerComponents\Title;

class SystemLogLayout extends Layout
{
    /**
     * @return string
     */
    public function title(): string
    {
        return __('global.mgrlog_view');
    }

    /**
     * @return string
     */
    public function icon(): string
    {
        return 'fa fa-user-secret';
    }

    /**
     * @return array
     */
    public function default(): array
    {
        return [
            Actions::make()
                ->setClear(__('global.clear_log'), '', 'btn-red', 'fa fa-trash'),

            Title::make()
                ->setTitle($this->title())
                ->setIcon($this->icon()),

            Panel::make()
                ->setModel('data')
                ->setId('system-log')
                ->setClass('mx-4 mb-4')
                ->setHistory(!0)
                ->addColumn('username', __('global.mgrlog_user'), sort: !0)
                ->addColumn(['action', 'message'], __('global.mgrlog_actionid'), sort: !0)
                ->addColumn('itemid', __('global.mgrlog_itemid'), sort: !0)
                ->addColumn('itemname', __('global.mgrlog_itemname'), sort: !0)
                ->addColumn('timestamp', __('global.mgrlog_time'), sort: !0)
                ->addColumn('ip', 'IP', sort: !0)
                ->addColumn('useragent', 'USER_AGENT', sort: !0),
        ];
    }
}
