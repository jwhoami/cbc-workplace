<?php

namespace App\Filament\Admin\Resources\CandidateProfileResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class EducationsRelationManager extends RelationManager
{
  protected static string $relationship = 'educations';

  public static function getTitle($ownerRecord, string $pageClass): string
  {
    return __('models/education.plural-label');
  }

  public function table(Table $table): Table
  {
    return $table
      ->defaultSort('graduation_year', 'desc')
      ->columns([
        Tables\Columns\TextColumn::make('institution')
          ->label(__('models/education.table.columns.institution')),
        Tables\Columns\TextColumn::make('degree')
          ->label(__('models/education.table.columns.degree')),
        Tables\Columns\TextColumn::make('field_of_study')
          ->label(__('models/education.table.columns.field_of_study')),
        Tables\Columns\TextColumn::make('graduation_year')
          ->label(__('models/education.table.columns.graduation_year'))
          ->placeholder('—'),
        Tables\Columns\IconColumn::make('is_in_progress')
          ->boolean()
          ->label(__('models/education.table.columns.is_in_progress')),
      ]);
  }
}
