<?php

namespace App\Filament\Admin\Resources\VentureResource\Pages;

use App\Filament\Admin\Resources\VentureResource;
use App\Filament\Shared\Resources\BaseVentureResource\Pages\BaseEditVenture;
use App\Models\Category;
use Illuminate\Database\Eloquent\Model;

class EditVenture extends BaseEditVenture
{
  protected static string $resource = VentureResource::class;

}
