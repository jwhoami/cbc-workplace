<?php

namespace App\Filament\Admin\Resources\CandidateProfileResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class WorkExperiencesRelationManager extends RelationManager
{
    protected static string $relationship = 'workExperiences';

    public static function getTitle($ownerRecord, string $pageClass): string
    {
        return __('models/work-experience.plural-label');
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('start_date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('company')
                    ->label(__('models/work-experience.table.columns.company')),
                Tables\Columns\TextColumn::make('position')
                    ->label(__('models/work-experience.table.columns.position')),
                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->label(__('models/work-experience.table.columns.start_date')),
                Tables\Columns\TextColumn::make('end_date')
                    ->date()
                    ->label(__('models/work-experience.table.columns.end_date'))
                    ->placeholder('—'),
                Tables\Columns\IconColumn::make('is_current')
                    ->boolean()
                    ->label(__('models/work-experience.table.columns.is_current')),
            ]);
    }
}
