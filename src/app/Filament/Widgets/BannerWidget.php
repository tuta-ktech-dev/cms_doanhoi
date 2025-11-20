<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class BannerWidget extends Widget
{
    protected string $view = 'filament.widgets.banner-widget';
    
    protected int | string | array $columnSpan = 'full';
    
    protected static ?int $sort = -1; // Hiển thị đầu tiên
}

