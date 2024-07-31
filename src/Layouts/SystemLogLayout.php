<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Illuminate\Support\Facades\Lang;
use Team64j\LaravelManagerComponents\Actions;
use Team64j\LaravelManagerComponents\Panel;
use Team64j\LaravelManagerComponents\Tabs;
use Team64j\LaravelManagerComponents\Title;

class SystemLogLayout extends Layout
{
    /**
     * @return string
     */
    public function title(): string
    {
        return Lang::get('global.mgrlog_view');
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
                ->setClear(Lang::get('global.clear_log'), '', 'btn-red', 'fa fa-trash'),

            Title::make()
                ->setTitle($this->title())
                ->setIcon($this->icon()),

            Tabs::make()
                ->addTab('default', slot: [
                    Panel::make()
                        ->setId('system-log')
                        ->setModel('data')
                        ->setHistory(!0)
                        ->addColumn('username', Lang::get('global.mgrlog_user'), sort: !0)
                        ->addColumn(['action', 'message'], Lang::get('global.mgrlog_actionid'), sort: !0)
                        ->addColumn('itemid', Lang::get('global.mgrlog_itemid'), sort: !0)
                        ->addColumn('itemname', Lang::get('global.mgrlog_itemname'), sort: !0)
                        ->addColumn('timestamp', Lang::get('global.mgrlog_time'), sort: !0)
                        ->addColumn('ip', 'IP', sort: !0)
                        ->addColumn('useragent', 'USER_AGENT', sort: !0),
                ]),
        ];
    }
}
