<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use EvolutionCMS\Models\EventLog;
use Illuminate\Support\Facades\Lang;
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
        return [
            ActionsButtons::make()
                ->setClear(Lang::get('global.clear_log'), '', 'btn-red', 'fa fa-trash'),

            Title::make()
                ->setTitle(Lang::get('global.eventlog_viewer'))
                ->setIcon('fa fa-exclamation-triangle'),

            Panel::make()
                ->setModel('data')
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
                ->addColumn(['user', 'users.username'], Lang::get('global.user'), ['width' => '20rem']),
        ];
    }

    /**
     * @param EventLog|null $model
     *
     * @return array
     */
    public function default(EventLog $model = null): array
    {
        return [
            ActionsButtons::make()
                ->setCancel()
                ->setCancelTo([
                    'name' => 'EventLogs',
                    'close' => true,
                ])
                ->setDelete()
                ->setDeleteClass('btn-red'),

            Title::make()
                ->setTitle(Lang::get('global.eventlog'))
                ->setIcon('fa fa-exclamation-triangle'),

            '<div class="mx-4 mb-4 rounded border p-6 bg-white dark:bg-gray-700 overflow-auto">
              <div class="data data-event-log mb-4">
                <table class="w-full">
                  <thead>
                  <tr>
                    <th colspan="4" class="text-lg pb-4">' . e($model->source . ' - ' . Lang::get('global.eventlog_viewer')) . '</th>
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
        
            </div>',
        ];
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return 'fa fa-exclamation-triangle';
    }
}
