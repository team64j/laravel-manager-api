<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Illuminate\Support\Facades\Lang;
use Team64j\LaravelManagerApi\Components\ActionsButtons;
use Team64j\LaravelManagerApi\Components\Panel;
use Team64j\LaravelManagerApi\Components\Title;

class SystemLogLayout extends Layout
{
    /**
     * @return array
     */
    public function list(): array
    {
        $data[] = ActionsButtons::make()
            ->setClear(Lang::get('global.clear_log'), '', 'btn-red', 'fa fa-trash');

        $data[] = Title::make()
            ->setTitle(Lang::get('global.mgrlog_view'))
            ->setIcon('fa fa-user-secret');

        $data[] = Panel::make()
            ->setId('system-log')
            ->setModel('data')
            ->setHistory(true)
            ->setClass('py-4')
            ->addColumn('username', Lang::get('global.mgrlog_user'), [], true)
            ->addColumn(['action', 'message'], Lang::get('global.mgrlog_actionid'), [], true)
            ->addColumn('itemid', Lang::get('global.mgrlog_itemid'), [], true)
            ->addColumn('itemname', Lang::get('global.mgrlog_itemname'), [], true)
            ->addColumn('timestamp', Lang::get('global.mgrlog_time'), [], true)
            ->addColumn('ip', 'IP', [], true)
            ->addColumn('useragent', 'USER_AGENT', [], true);

        return $data;
    }

    /**
     * @return array
     */
    public function title(): array
    {
        return [
            'title' => Lang::get('global.mgrlog_view'),
            'icon' => 'fa fa-user-secret',
        ];
    }
}
