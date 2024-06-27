<?php

namespace App\Filament\Pages;

use App\Filament\Resources\StatsResource\Widgets\StatsOverview as StatsOverview;

class Dashboard extends \Filament\Pages\Dashboard
{
    protected static string $routePath = '/';
    protected static ?string $title = 'Dashboard';
    protected ?string $subheading = 'Subheading';
    public string $status;

    protected function getHeaderWidgets(): array
    {
        return [
            StatsOverview::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            StatsOverview::class,
        ];
    }
}