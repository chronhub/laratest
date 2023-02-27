<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Projecting\OptionFactory;

final class HomeController
{
    public function __invoke(): string
    {
//        $options = new OptionFactory();
//        dump($options->toOptionsString());

        return 'ok';
    }
}
