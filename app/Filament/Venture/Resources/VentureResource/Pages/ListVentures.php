<?php

namespace App\Filament\Venture\Resources\VentureResource\Pages;

use App\Filament\Venture\Resources\VentureResource;
use Filament\Resources\Pages\ListRecords;

class ListVentures extends ListRecords
{
    protected static ?string $slug = '/';

    protected static string $resource = VentureResource::class;

    public function mount(): void
    {
        parent::mount();
    }
}
