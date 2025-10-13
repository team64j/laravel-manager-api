<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Team64j\LaravelManagerApi\Models\EventLog;
use Team64j\LaravelManagerComponents\Actions;
use Team64j\LaravelManagerComponents\Panel;
use Team64j\LaravelManagerComponents\Title;

class EventLogLayout extends Layout
{
    /**
     * @return string
     */
    public function icon(): string
    {
        return 'fa fa-exclamation-triangle';
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return __('global.eventlog');
    }

    /**
     * @return string
     */
    public function titleList(): string
    {
        return __('global.eventlog_viewer');
    }

    /**
     * @return array
     */
    public function list(): array
    {
        return [
            Actions::make()
                ->setClear(__('global.clear_log'), '', 'btn-red', 'fa fa-trash'),

            Title::make()
                ->setTitle($this->titleList())
                ->setIcon($this->icon()),

            Panel::make()
                ->setModel('data')
                ->setRoute('/event-log/:id')
                ->setHistory(true)
                ->setClass('mx-4 mb-4')
                ->addColumn(
                    'type',
                    __('global.type'),
                    [
                        'textAlign' => 'center',
                        'width' => '10rem',
                    ],
                    values: [
                        1 => '<i class="fa fa-info-circle text-blue-500"></i>',
                        2 => '<i class="fa fa-exclamation-triangle text-amber-400"></i>',
                        3 => '<i class="fa fa-times-circle text-rose-500"></i>',
                    ]
                )
                ->addColumn('source', __('global.source'))
                ->addColumn('createdon', __('global.date'), ['textAlign' => 'center', 'width' => '20rem'])
                ->addColumn('eventid', __('global.event_id'), ['textAlign' => 'center', 'width' => '10rem'])
                ->addColumn(['user', 'users.username'], __('global.user'), ['width' => '20rem']),
        ];
    }

    /**
     * @param EventLog|null $model
     *
     * @return array
     */
    public function default(?EventLog $model = null): array
    {
        return [
            Actions::make()
                ->setCancel()
                ->setCancelTo([
                    'name' => 'EventLogs',
                    'close' => true,
                ])
                ->setDelete()
                ->setDeleteClass('btn-red'),

            Title::make()
                ->setTitle($this->title())
                ->setIcon($this->icon()),

            '<div class="mx-4 mb-4 rounded border p-6 bg-white dark:bg-gray-700 overflow-auto">
              <div class="data data-event-log mb-4">
                <table class="w-full">
                  <thead>
                  <tr>
                    <th colspan="4" class="text-lg pb-4">' . e($model->source . ' - ' . $this->titleList()) . '</th>
                  </tr>
                  <tr>
                    <th>' . __('global.event_id') . '</th>
                    <th>' . __('global.source') . '</th>
                    <th>' . __('global.date') . '</th>
                    <th>' . __('global.user') . '</th>
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
}
