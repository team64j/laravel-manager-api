<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Illuminate\Support\Facades\Lang;
use Team64j\LaravelEvolution\Models\EventLog;
use Team64j\LaravelManagerApi\Components\ActionsButtons;
use Team64j\LaravelManagerApi\Components\Panel;
use Team64j\LaravelManagerApi\Components\Title;

class EventLogLayout extends Layout
{
    /**
     * @return array
     */
    public function list(): array
    {
        $data[] = ActionsButtons::make()
            ->setClear(Lang::get('global.clear_log'), '', 'btn-red', 'fa fa-trash');

        $data[] = Title::make()
            ->setTitle(Lang::get('global.eventlog_viewer'))
            ->setIcon('fa fa-exclamation-triangle');

        $data[] = Panel::make()
            ->setModel('data')
            ->setClass('py-4')
            ->setRoute('EventLog')
            ->addColumn(
                'type',
                Lang::get('global.type'),
                [
                    'textAlign' => 'center',
                    'width' => '10rem',
                ],
                false,
                [
                    1 => '<i class="fa fa-info-circle text-blue-500"></i>',
                    2 => '<i class="fa fa-exclamation-triangle text-amber-400"></i>',
                    3 => '<i class="fa fa-times-circle text-rose-500"></i>',
                ]
            )
            ->addColumn('source', Lang::get('global.source'))
            ->addColumn('createdon', Lang::get('global.date'), ['textAlign' => 'center', 'width' => '20rem'])
            ->addColumn('eventid', Lang::get('global.event_id'), ['textAlign' => 'center', 'width' => '10rem'])
            ->addColumn(['user', 'users.username'], Lang::get('global.user'), ['width' => '20rem']);

        return $data;
    }

    /**
     * @param EventLog|null $model
     *
     * @return array
     */
    public function default(EventLog $model = null): array
    {
        $data[] = ActionsButtons::make()
            ->setDelete()
            ->setDeleteClass('btn-red')
            ->setCancel();

        $data[] = Title::make()
            ->setTitle(Lang::get('global.eventlog'))
            ->setIcon('fa fa-exclamation-triangle');

        $data[] = '
            <div class="py-4 bg-white dark:bg-gray-700">
              <div class="data data-event-log mb-4">
                <table>
                  <thead>
                  <tr>
                    <th colspan="4">' . e($model->source . ' - ' . Lang::get('global.eventlog_viewer')) . '</th>
                  </tr>
                  <tr>
                    <th>' . Lang::get('global.event_id') . '</th>
                    <th>' . Lang::get('global.source') . '</th>
                    <th>' . Lang::get('global.date') . '</th>
                    <th>' . Lang::get('global.user') . '</th>
                  </tr>
                  </thead>
                  <tbody>
                  <tr class="text-center">
                    <td>' . $model->eventid . '</td>
                    <td>' . e($model->source) . '</td>
                    <td>' . $model->createdon . '</td>
                    <td>' . ($model->users->username ?? '-') . '</td>
                  </tr>
                  </tbody>
                </table>
              </div>
        
              <div class="data data-event-log mb-4">' . $model->description . '</div>
        
            </div>';

        return $data;
    }

    /**
     * @return array
     */
    public function tabList(): array
    {
        return [
            'title' => Lang::get('global.eventlog_viewer'),
            'icon' => 'fa fa-exclamation-triangle',
        ];
    }

    /**
     * @return array
     */
    public function title(): array
    {
        return [
            'title' => Lang::get('global.eventlog'),
            'icon' => 'fa fa-exclamation-triangle',
        ];
    }
}
