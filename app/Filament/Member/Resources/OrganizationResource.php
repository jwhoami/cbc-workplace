<?php

namespace App\Filament\Member\Resources;

use App\Enums\OrganizationType;
use App\Filament\Member\Resources\OrganizationResource\Pages;
use App\Models\Organization;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrganizationResource extends Resource
{
    protected static ?string $model = Organization::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static bool $shouldSkipAuthorization = true;

    public static function getNavigationGroup(): ?string
    {
        return __('models/organization.navigation.group');
    }

    public static function getModelLabel(): string
    {
        return __('models/organization.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('models/organization.plural-label');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('member_id', auth('member')->id());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('models/organization.sections.general'))
                    ->schema([
                        Forms\Components\TextInput::make('legal_name')
                            ->required()
                            ->maxLength(150)
                            ->label(__('models/organization.fields.legal_name'))
                            ->placeholder(__('models/organization.form.placeholders.legal_name')),
                        Forms\Components\TextInput::make('display_name')
                            ->required()
                            ->maxLength(150)
                            ->label(__('models/organization.fields.display_name'))
                            ->placeholder(__('models/organization.form.placeholders.display_name')),
                        Forms\Components\Select::make('type')
                            ->required()
                            ->options(OrganizationType::class)
                            ->label(__('models/organization.fields.type'))
                            ->live(),
                        Forms\Components\TextInput::make('denomination')
                            ->maxLength(100)
                            ->label(__('models/organization.fields.denomination'))
                            ->placeholder(__('models/organization.form.placeholders.denomination'))
                            ->visible(fn (Forms\Get $get): bool => in_array($get('type'), [
                                OrganizationType::CHURCH->value,
                                OrganizationType::MINISTRY->value,
                            ])),
                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->label(__('models/organization.fields.description'))
                            ->placeholder(__('models/organization.form.placeholders.description'))
                            ->rows(4),
                        Forms\Components\Textarea::make('culture_statement')
                            ->label(__('models/organization.fields.culture_statement'))
                            ->placeholder(__('models/organization.form.placeholders.culture_statement'))
                            ->rows(3),
                        Forms\Components\FileUpload::make('logo')
                            ->image()
                            ->maxSize(2048)
                            ->directory('organizations/logos')
                            ->disk('public')
                            ->label(__('models/organization.fields.logo')),
                        Forms\Components\TextInput::make('website')
                            ->url()
                            ->maxLength(255)
                            ->label(__('models/organization.fields.website'))
                            ->placeholder(__('models/organization.form.placeholders.website')),
                    ])
                    ->columns(2),

                Forms\Components\Section::make(__('models/organization.sections.contact'))
                    ->schema([
                        Forms\Components\TextInput::make('email_contact')
                            ->required()
                            ->email()
                            ->maxLength(150)
                            ->label(__('models/organization.fields.email_contact'))
                            ->placeholder(__('models/organization.form.placeholders.email_contact')),
                        Forms\Components\TextInput::make('phone')
                            ->maxLength(30)
                            ->label(__('models/organization.fields.phone'))
                            ->placeholder(__('models/organization.form.placeholders.phone')),
                        Forms\Components\TextInput::make('city')
                            ->required()
                            ->maxLength(100)
                            ->label(__('models/organization.fields.city'))
                            ->placeholder(__('models/organization.form.placeholders.city')),
                        Forms\Components\TextInput::make('province')
                            ->required()
                            ->maxLength(100)
                            ->label(__('models/organization.fields.province'))
                            ->placeholder(__('models/organization.form.placeholders.province')),
                        Forms\Components\TextInput::make('country')
                            ->required()
                            ->maxLength(100)
                            ->default('Panama')
                            ->label(__('models/organization.fields.country'))
                            ->placeholder(__('models/organization.form.placeholders.country')),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('display_name')
                    ->searchable()
                    ->label(__('models/organization.fields.display_name')),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->label(__('models/organization.fields.type')),
                Tables\Columns\TextColumn::make('verification_state')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        \App\Enums\OrganizationVerificationState::PENDING => 'warning',
                        \App\Enums\OrganizationVerificationState::VERIFIED => 'success',
                        \App\Enums\OrganizationVerificationState::SUSPENDED => 'danger',
                        default => 'gray',
                    })
                    ->label(__('models/organization.fields.verification_state')),
                Tables\Columns\TextColumn::make('city')
                    ->label(__('models/organization.fields.city')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrganizations::route('/'),
            'create' => Pages\CreateOrganization::route('/create'),
            'edit' => Pages\EditOrganization::route('/{record}/edit'),
        ];
    }
}
