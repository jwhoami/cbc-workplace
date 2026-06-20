<?php

namespace App\Filament\Admin\Resources\TextResource\Pages;

use App\Filament\Admin\Resources\TextResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewText extends ViewRecord
{
    protected static string $resource = TextResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('goto-list')
                ->label(__('common.actions.goto-list.label'))
                ->tooltip(__('common.actions.goto-list.tooltip'))
                ->color('gray')
                ->url(TextResource::getUrl('index')),
            Actions\EditAction::make()
                ->label(__('common.actions.edit.label'))
                ->tooltip(__('common.actions.edit.tooltip')),
        ];
    }
}
