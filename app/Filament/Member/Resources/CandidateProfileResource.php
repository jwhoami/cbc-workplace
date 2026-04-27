<?php

namespace App\Filament\Member\Resources;

use App\Filament\Member\Resources\CandidateProfileResource\Pages;
use App\Filament\Member\Resources\CandidateProfileResource\RelationManagers;
use App\Models\CandidateProfile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CandidateProfileResource extends Resource
{
    protected static ?string $model = CandidateProfile::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static bool $shouldSkipAuthorization = true;

    public static function getNavigationLabel(): string
    {
        return __('models/candidate-profile.navigation.label');
    }

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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('member_id', auth('member')->id());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('models/candidate-profile.sections.professional'))
                    ->schema([
                        Forms\Components\TextInput::make('headline')
                            ->required()
                            ->maxLength(150)
                            ->label(__('models/candidate-profile.fields.headline'))
                            ->placeholder(__('models/candidate-profile.form.placeholders.headline')),
                        Forms\Components\Textarea::make('summary')
                            ->required()
                            ->label(__('models/candidate-profile.fields.summary'))
                            ->placeholder(__('models/candidate-profile.form.placeholders.summary'))
                            ->rows(4),
                        Forms\Components\Textarea::make('faith_statement')
                            ->label(__('models/candidate-profile.fields.faith_statement'))
                            ->placeholder(__('models/candidate-profile.form.placeholders.faith_statement'))
                            ->rows(3),
                    ]),

                Forms\Components\Section::make(__('models/candidate-profile.sections.location'))
                    ->schema([
                        Forms\Components\TextInput::make('city')
                            ->required()
                            ->maxLength(100)
                            ->label(__('models/candidate-profile.fields.city'))
                            ->placeholder(__('models/candidate-profile.form.placeholders.city')),
                        Forms\Components\TextInput::make('province')
                            ->required()
                            ->maxLength(100)
                            ->label(__('models/candidate-profile.fields.province'))
                            ->placeholder(__('models/candidate-profile.form.placeholders.province')),
                        Forms\Components\TextInput::make('phone')
                            ->required()
                            ->maxLength(30)
                            ->label(__('models/candidate-profile.fields.phone'))
                            ->placeholder(__('models/candidate-profile.form.placeholders.phone')),
                    ])
                    ->columns(2),

                Forms\Components\Section::make(__('models/candidate-profile.sections.files'))
                    ->schema([
                        Forms\Components\FileUpload::make('photo')
                            ->image()
                            ->maxSize(2048)
                            ->directory('candidates/photos')
                            ->disk('public')
                            ->label(__('models/candidate-profile.fields.photo'))
                            ->helperText(__('models/candidate-profile.form.helpers.photo')),
                        Forms\Components\FileUpload::make('cv_path')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(5120)
                            ->directory('candidates/cvs')
                            ->disk('public')
                            ->label(__('models/candidate-profile.fields.cv_path'))
                            ->helperText(__('models/candidate-profile.form.helpers.cv_path')),
                    ])
                    ->columns(2),

                Forms\Components\Section::make(__('models/candidate-profile.sections.visibility'))
                    ->schema([
                        Forms\Components\Toggle::make('is_visible')
                            ->default(true)
                            ->label(__('models/candidate-profile.fields.is_visible'))
                            ->helperText(__('models/candidate-profile.form.helpers.is_visible')),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('headline')
                    ->searchable()
                    ->label(__('models/candidate-profile.fields.headline')),
                Tables\Columns\TextColumn::make('city')
                    ->label(__('models/candidate-profile.fields.city')),
                Tables\Columns\IconColumn::make('is_visible')
                    ->boolean()
                    ->label(__('models/candidate-profile.fields.is_visible')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'create' => Pages\CreateCandidateProfile::route('/create'),
            'edit' => Pages\EditCandidateProfile::route('/{record}/edit'),
        ];
    }
}
