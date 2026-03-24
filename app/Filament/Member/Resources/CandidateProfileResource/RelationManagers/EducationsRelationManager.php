<?php

namespace App\Filament\Member\Resources\CandidateProfileResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
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

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('institution')
                    ->required()
                    ->maxLength(200)
                    ->label(__('models/education.fields.institution'))
                    ->placeholder(__('models/education.form.placeholders.institution')),
                Forms\Components\TextInput::make('degree')
                    ->required()
                    ->maxLength(200)
                    ->label(__('models/education.fields.degree'))
                    ->placeholder(__('models/education.form.placeholders.degree')),
                Forms\Components\TextInput::make('field_of_study')
                    ->required()
                    ->maxLength(150)
                    ->label(__('models/education.fields.field_of_study'))
                    ->placeholder(__('models/education.form.placeholders.field_of_study')),
                Forms\Components\Toggle::make('is_in_progress')
                    ->label(__('models/education.fields.is_in_progress'))
                    ->live()
                    ->afterStateUpdated(function (Forms\Set $set, $state) {
                        if ($state) {
                            $set('graduation_year', null);
                        }
                    }),
                Forms\Components\TextInput::make('graduation_year')
                    ->numeric()
                    ->minValue(1950)
                    ->maxValue(date('Y') + 5)
                    ->label(__('models/education.fields.graduation_year'))
                    ->placeholder(__('models/education.form.placeholders.graduation_year'))
                    ->requiredUnless('is_in_progress', true)
                    ->visible(fn (Forms\Get $get): bool => ! $get('is_in_progress')),
            ]);
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
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
