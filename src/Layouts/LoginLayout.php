<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Illuminate\Support\Facades\Lang;
use Team64j\LaravelManagerComponents\Button;
use Team64j\LaravelManagerComponents\Checkbox;
use Team64j\LaravelManagerComponents\Input;
use Team64j\LaravelManagerComponents\Template;

class LoginLayout extends Layout
{
    /**
     * @return array
     */
    public function default(): array
    {
        return [
            Input::make('username')
                ->setId('username')
                ->setLabel(Lang::get('global.username'))
                ->setInputClass('!bg-transparent input-lg')
                ->setErrorClass('hidden'),

            Input::make('password')
                ->setId('password')
                ->setType('password')
                ->setLabel(Lang::get('global.password'))
                ->setInputClass('!bg-transparent input-lg')
                ->setErrorClass('hidden'),

            Template::make()
                ->setClass('flex justify-between items-center')
                ->setSlot([
                    Checkbox::make('remember')
                        ->setId('remember')
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
