<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Illuminate\Support\Facades\Lang;
use Team64j\LaravelManagerApi\Components\Button;
use Team64j\LaravelManagerApi\Components\Checkbox;
use Team64j\LaravelManagerApi\Components\Input;
use Team64j\LaravelManagerApi\Components\Template;

class LoginLayout extends Layout
{
    /**
     * @return array
     */
    public function default(): array
    {
        return [
            Input::make('username')
                ->setLabel(Lang::get('global.username'))
                ->setInputClass('!bg-transparent input-lg')
                ->setErrorClass('hidden'),

            Input::make('password')
                ->setType('password')
                ->setLabel(Lang::get('global.password'))
                ->setInputClass('!bg-transparent input-lg')
                ->setErrorClass('hidden'),

            Template::make()
                ->setClass('flex justify-between items-center')
                ->setSlot([
                    Checkbox::make('remember')
                        ->setLabel(Lang::get('global.remember_username'))
                        ->setClass('inline-flex')
                        ->setInputClass('input-lg'),

                    Button::make()
                        ->setValue(Lang::get('global.login_button'))
                        ->setInputClass('btn-green btn-lg whitespace-nowrap'),
                ])
        ];
    }
}
