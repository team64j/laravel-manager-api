<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Illuminate\Support\Facades\Lang;
use Team64j\LaravelManagerApi\Components\Title;

class FilesLayout extends Layout
{
    /**
     * @return array
     */
    public function default(): array
    {
        $data[] = Title::make()
            ->setTitle(Lang::get('global.files_management'))
            ->setIcon('far fa-folder-open');

        return $data;
    }
}
