<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class BoasVindas extends Widget
{
    protected static string $view = 'filament.widgets.boas-vindas';

    public function getColumnSpan(): int | string | array
    {
        return 'full';
    }
}
