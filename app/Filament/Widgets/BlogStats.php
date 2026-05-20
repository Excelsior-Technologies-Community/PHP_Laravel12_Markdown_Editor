<?php

namespace App\Filament\Widgets;

use App\Models\Post;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class BlogStats extends BaseWidget
{
    protected function getCards(): array
    {
        return [

            Card::make('Total Posts', Post::count()),

            Card::make(
                'Published Posts',
                Post::where('is_published', true)->count()
            ),

            Card::make(
                'Draft Posts',
                Post::where('is_published', false)->count()
            ),

        ];
    }
}