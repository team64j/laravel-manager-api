<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

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
                ->setLabel(__('global.username'))
                ->setInputClass('!bg-transparent input-lg')
                ->setErrorClass('hidden'),

            Input::make('password')
                ->setId('password')
                ->setType('password')
                ->setLabel(__('global.password'))
                ->setInputClass('!bg-transparent input-lg')
                ->setErrorClass('hidden'),

            Template::make()
                ->setClass('flex justify-between items-center')
                ->setSlot([
                    Checkbox::make('remember')
                        ->setId('remember')
                        ->setLabel(__('global.remember_username'))
                        ->setClass('inline-flex')
                        ->setInputClass('input-lg'),

                    Button::make()
                        ->setValue(__('global.login_button'))
                        ->setInputClass('btn-green btn-lg whitespace-nowrap'),
                ]),
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return '';
    }

    /**
     * @return string
     */
    public function icon(): string
    {
        return '';
    }
}
