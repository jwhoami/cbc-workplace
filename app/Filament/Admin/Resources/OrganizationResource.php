<?php

namespace App\Filament\Admin\Resources;

use App\Enums\OrganizationType;
use App\Enums\OrganizationVerificationState;
use App\Filament\Admin\Resources\OrganizationResource\Pages;
use App\Models\Organization;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrganizationResource extends Resource
{
    protected static ?string $model = Organization::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

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

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make(__('models/organization.sections.general'))
                    ->schema([
                        Infolists\Components\TextEntry::make('legal_name')
                            ->label(__('models/organization.fields.legal_name')),
                        Infolists\Components\TextEntry::make('display_name')
                            ->label(__('models/organization.fields.display_name')),
                        Infolists\Components\TextEntry::make('type')
                            ->badge()
                            ->label(__('models/organization.fields.type')),
                        Infolists\Components\TextEntry::make('denomination')
                            ->label(__('models/organization.fields.denomination'))
                            ->visible(fn (Organization $record): bool => $record->denomination !== null),
                        Infolists\Components\TextEntry::make('description')
                            ->label(__('models/organization.fields.description'))
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('culture_statement')
                            ->label(__('models/organization.fields.culture_statement'))
                            ->visible(fn (Organization $record): bool => $record->culture_statement !== null)
                            ->columnSpanFull(),
                        Infolists\Components\ImageEntry::make('logo')
                            ->label(__('models/organization.fields.logo'))
                            ->disk('public')
                            ->visible(fn (Organization $record): bool => $record->logo !== null),
                        Infolists\Components\TextEntry::make('website')
                            ->label(__('models/organization.fields.website'))
                            ->url(fn (Organization $record): ?string => $record->website)
                            ->openUrlInNewTab()
                            ->visible(fn (Organization $record): bool => $record->website !== null),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make(__('models/organization.sections.contact'))
                    ->schema([
                        Infolists\Components\TextEntry::make('email_contact')
                            ->label(__('models/organization.fields.email_contact')),
                        Infolists\Components\TextEntry::make('phone')
                            ->label(__('models/organization.fields.phone'))
                            ->visible(fn (Organization $record): bool => $record->phone !== null),
                        Infolists\Components\TextEntry::make('city')
                            ->label(__('models/organization.fields.city')),
                        Infolists\Components\TextEntry::make('province')
                            ->label(__('models/organization.fields.province')),
                        Infolists\Components\TextEntry::make('country')
                            ->label(__('models/organization.fields.country')),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make(__('models/organization.sections.verification'))
                    ->schema([
                        Infolists\Components\TextEntry::make('verification_state')
                            ->badge()
                            ->color(fn ($state) => match ($state) {
                                OrganizationVerificationState::PENDING => 'warning',
                                OrganizationVerificationState::VERIFIED => 'success',
                                OrganizationVerificationState::SUSPENDED => 'danger',
                                default => 'gray',
                            })
                            ->label(__('models/organization.fields.verification_state')),
                        Infolists\Components\TextEntry::make('verification_by')
                            ->label(__('models/organization.fields.verification_by'))
                            ->visible(fn (Organization $record): bool => $record->verification_by !== null),
                        Infolists\Components\TextEntry::make('verified_at')
                            ->dateTime()
                            ->label(__('models/organization.fields.verified_at'))
                            ->visible(fn (Organization $record): bool => $record->verified_at !== null),
                        Infolists\Components\TextEntry::make('verification_reason')
                            ->label(__('models/organization.fields.verification_reason'))
                            ->visible(fn (Organization $record): bool => $record->verification_reason !== null)
                            ->columnSpanFull(),
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
                    ->sortable()
                    ->label(__('models/organization.fields.display_name')),
                Tables\Columns\TextColumn::make('legal_name')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('models/organization.fields.legal_name')),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->label(__('models/organization.fields.type')),
                Tables\Columns\TextColumn::make('verification_state')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        OrganizationVerificationState::PENDING => 'warning',
                        OrganizationVerificationState::VERIFIED => 'success',
                        OrganizationVerificationState::SUSPENDED => 'danger',
                        default => 'gray',
                    })
                    ->label(__('models/organization.fields.verification_state')),
                Tables\Columns\TextColumn::make('city')
                    ->label(__('models/organization.fields.city')),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label(__('models/organization.fields.created_at')),
            ])
            ->filters([
            Tables\Filters\SelectFilter::make('verification_state')
                    ->options(OrganizationVerificationState::class)
                    ->label(__('models/organization.fields.verification_state')),
            Tables\Filters\SelectFilter::make('type')
                    ->options(OrganizationType::class)
                    ->label(__('models/organization.fields.type')),
        ])
            ->actions([
            Tables\Actions\ViewAction::make(),
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
            'view' => Pages\ViewOrganization::route('/{record}'),
        ];
    }
}
