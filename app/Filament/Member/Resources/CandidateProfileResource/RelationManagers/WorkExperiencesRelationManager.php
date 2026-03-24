<?php

namespace App\Filament\Member\Resources\CandidateProfileResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
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

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('company')
                    ->required()
                    ->maxLength(150)
                    ->label(__('models/work-experience.fields.company'))
                    ->placeholder(__('models/work-experience.form.placeholders.company')),
                Forms\Components\TextInput::make('position')
                    ->required()
                    ->maxLength(150)
                    ->label(__('models/work-experience.fields.position'))
                    ->placeholder(__('models/work-experience.form.placeholders.position')),
                Forms\Components\Textarea::make('description')
                    ->required()
                    ->label(__('models/work-experience.fields.description'))
                    ->placeholder(__('models/work-experience.form.placeholders.description'))
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\DatePicker::make('start_date')
                    ->required()
                    ->label(__('models/work-experience.fields.start_date')),
                Forms\Components\Toggle::make('is_current')
                    ->label(__('models/work-experience.fields.is_current'))
                    ->live()
                    ->afterStateUpdated(function (Forms\Set $set, $state) {
                        if ($state) {
                            $set('end_date', null);
                        }
                    }),
                Forms\Components\DatePicker::make('end_date')
                    ->label(__('models/work-experience.fields.end_date'))
                    ->requiredUnless('is_current', true)
                    ->visible(fn (Forms\Get $get): bool => ! $get('is_current')),
            ]);
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
