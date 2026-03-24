<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CandidateProfileResource\Pages;
use App\Filament\Admin\Resources\CandidateProfileResource\RelationManagers;
use App\Models\CandidateProfile;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CandidateProfileResource extends Resource
{
  protected static ?string $model = CandidateProfile::class;

  protected static ?string $navigationIcon = 'heroicon-o-user-circle';

  public static function getNavigationGroup(): ?string
  {
    return __('models/candidate-profile.navigation.group');
  }

  public static function getModelLabel(): string
  {
    return __('models/candidate-profile.label');
  }

  public static function getPluralModelLabel(): string
  {
    return __('models/candidate-profile.plural-label');
  }

  public static function infolist(Infolist $infolist): Infolist
  {
    return $infolist
      ->schema([
        Infolists\Components\Section::make(__('models/candidate-profile.sections.professional'))
          ->schema([
            Infolists\Components\TextEntry::make('headline')
              ->label(__('models/candidate-profile.fields.headline')),
            Infolists\Components\TextEntry::make('summary')
              ->label(__('models/candidate-profile.fields.summary'))
              ->columnSpanFull(),
            Infolists\Components\TextEntry::make('faith_statement')
              ->label(__('models/candidate-profile.fields.faith_statement'))
              ->visible(fn (CandidateProfile $record): bool => $record->faith_statement !== null)
              ->columnSpanFull(),
          ])
          ->columns(2),

        Infolists\Components\Section::make(__('models/candidate-profile.sections.location'))
          ->schema([
            Infolists\Components\TextEntry::make('city')
              ->label(__('models/candidate-profile.fields.city')),
            Infolists\Components\TextEntry::make('province')
              ->label(__('models/candidate-profile.fields.province')),
            Infolists\Components\TextEntry::make('phone')
              ->label(__('models/candidate-profile.fields.phone')),
          ])
          ->columns(2),

        Infolists\Components\Section::make(__('models/candidate-profile.sections.files'))
          ->schema([
            Infolists\Components\ImageEntry::make('photo')
              ->label(__('models/candidate-profile.fields.photo'))
              ->disk('public')
              ->visible(fn (CandidateProfile $record): bool => $record->photo !== null),
            Infolists\Components\TextEntry::make('cv_path')
              ->label(__('models/candidate-profile.fields.cv_path'))
              ->visible(fn (CandidateProfile $record): bool => $record->cv_path !== null)
              ->url(fn (CandidateProfile $record): ?string => $record->cv_path ? asset('storage/' . $record->cv_path) : null)
              ->openUrlInNewTab(),
          ])
          ->columns(2),

        Infolists\Components\Section::make(__('models/candidate-profile.sections.visibility'))
          ->schema([
            Infolists\Components\IconEntry::make('is_visible')
              ->boolean()
              ->label(__('models/candidate-profile.fields.is_visible')),
          ]),

        Infolists\Components\Section::make(__('models/candidate-profile.sections.member_info'))
          ->schema([
            Infolists\Components\TextEntry::make('member.name')
              ->label(__('models/candidate-profile.fields.member')),
            Infolists\Components\TextEntry::make('member.email')
              ->label('Email'),
          ])
          ->columns(2),
      ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\TextColumn::make('member.name')
          ->searchable()
          ->sortable()
          ->label(__('models/candidate-profile.table.columns.member_name')),
        Tables\Columns\TextColumn::make('headline')
          ->searchable()
          ->label(__('models/candidate-profile.table.columns.headline')),
        Tables\Columns\TextColumn::make('city')
          ->label(__('models/candidate-profile.table.columns.city')),
        Tables\Columns\IconColumn::make('is_visible')
          ->boolean()
          ->label(__('models/candidate-profile.table.columns.is_visible')),
      ])
      ->filters([
        Tables\Filters\SelectFilter::make('is_visible')
          ->options([
            '1' => __('models/candidate-profile.fields.is_visible'),
            '0' => 'Oculto',
          ])
          ->label(__('models/candidate-profile.fields.is_visible')),
      ])
      ->actions([
        Tables\Actions\ViewAction::make(),
      ]);
  }

  public static function getRelations(): array
  {
    return [
      RelationManagers\WorkExperiencesRelationManager::class,
      RelationManagers\EducationsRelationManager::class,
    ];
  }

  public static function getPages(): array
  {
    return [
      'index' => Pages\ListCandidateProfiles::route('/'),
      'view' => Pages\ViewCandidateProfile::route('/{record}'),
    ];
  }
}
