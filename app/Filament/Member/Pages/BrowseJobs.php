<?php

namespace App\Filament\Member\Pages;

use App\Models\JobListing;
use App\Enums\JobListingState;
use Filament\Pages\Page;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;

class BrowseJobs extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-magnifying-glass';

    protected static string $view = 'filament.member.pages.browse-jobs';

    protected static bool $shouldRegisterNavigation = false;

    public static function getNavigationLabel(): string
    {
        return __('Buscar Empleo');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Bolsa de Trabajo');
    }

    public function getTitle(): string
    {
        return __('Buscar Empleo');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                JobListing::query()
                    ->where('state', JobListingState::ACTIVE)
                    ->whereHas('organization', function (Builder $query) {
                        $query->excludingSuspended();
                    })
            )
            ->columns([
                TextColumn::make('title')
                    ->label(__('Título'))
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                TextColumn::make('organization.display_name')
                    ->label(__('Organización'))
                    ->searchable()
                    ->limit(40),
                TextColumn::make('city')
                    ->label(__('Ciudad'))
                    ->sortable(),
                TextColumn::make('contract_type')
                    ->label(__('Contrato')),
                TextColumn::make('work_modality')
                    ->label(__('Modalidad')),
            ])
            ->actions([
                Action::make('view')
                    ->label(__('Ver Detalle'))
                    ->icon('heroicon-o-eye')
                    ->url(fn (JobListing $record): string => url('/bolsa-de-trabajo/' . $record->slug))
            ]);
    }
}
