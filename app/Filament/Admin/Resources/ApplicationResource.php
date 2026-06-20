<?php

namespace App\Filament\Admin\Resources;

use App\Enums\ApplicationStatus;
use App\Filament\Admin\Resources\ApplicationResource\Pages;
use App\Models\Application;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ApplicationResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static ?string $navigationIcon = 'heroicon-o-paper-airplane';

    public static function getNavigationLabel(): string
    {
        return __('models/application.navigation.admin-label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.bolsa-de-trabajo');
    }

    public static function getModelLabel(): string
    {
        return __('models/application.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('models/application.plural-label');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('candidate_name_snapshot')
                    ->label(__('models/application.fields.candidate_name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('jobListing.title')
                    ->label(__('models/application.fields.job_listing'))
                    ->limit(40)
                    ->searchable(),
                Tables\Columns\TextColumn::make('jobListing.organization.display_name')
                    ->label(__('models/application.fields.organization'))
                    ->limit(30)
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('models/application.fields.status'))
                    ->badge(),
                Tables\Columns\TextColumn::make('submitted_at')
                    ->label(__('models/application.fields.submitted_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\IconColumn::make('anonymized_at')
                    ->label(__('models/application.fields.anonymized_at'))
                    ->boolean()
                    ->trueIcon('heroicon-o-shield-exclamation')
                    ->trueColor('warning')
                    ->falseIcon('heroicon-o-shield-check')
                    ->falseColor('success'),
            ])
            ->defaultSort('submitted_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('models/application.fields.status'))
                    ->options(collect(ApplicationStatus::cases())->mapWithKeys(
                        fn ($s) => [$s->value => $s->getLabel()]
                    )->all()),
                Tables\Filters\SelectFilter::make('organization_id')
                    ->label(__('models/application.fields.organization'))
                    ->relationship('jobListing.organization', 'display_name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('job_listing_id')
                    ->label(__('models/application.fields.job_listing'))
                    ->relationship('jobListing', 'title')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make()
                    ->columns(2)
                    ->schema([
                        Infolists\Components\TextEntry::make('candidate_name_snapshot')
                            ->label(__('models/application.fields.candidate_name')),
                        Infolists\Components\TextEntry::make('candidate_email_snapshot')
                            ->label(__('models/application.fields.candidate_email'))
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('jobListing.title')
                            ->label(__('models/application.fields.job_listing')),
                        Infolists\Components\TextEntry::make('jobListing.organization.display_name')
                            ->label(__('models/application.fields.organization')),
                        Infolists\Components\TextEntry::make('status')
                            ->label(__('models/application.fields.status'))
                            ->badge(),
                        Infolists\Components\TextEntry::make('submitted_at')
                            ->label(__('models/application.fields.submitted_at'))
                            ->dateTime('d/m/Y H:i'),
                        Infolists\Components\TextEntry::make('cover_letter')
                            ->label(__('models/application.fields.cover_letter'))
                            ->placeholder('—')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('cv_snapshot_filename')
                            ->label(__('models/application.fields.cv_snapshot'))
                            ->placeholder('—')
                            ->url(fn (Application $record) => $record->cv_snapshot_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($record->cv_snapshot_path) : null)
                            ->openUrlInNewTab()
                            ->color('primary')
                            ->underline()
                            ->icon('heroicon-o-document-arrow-down'),
                        Infolists\Components\TextEntry::make('last_status_changed_at')
                            ->label(__('models/application.fields.last_status_changed_at'))
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('last_status_changed_by')
                            ->label(__('models/application.fields.last_status_changed_by'))
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('anonymized_at')
                            ->label(__('models/application.fields.anonymized_at'))
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('—'),
                    ]),
                Infolists\Components\Section::make('Información del Candidato')
                    ->columns(2)
                    ->schema([
                        Infolists\Components\TextEntry::make('candidateProfile.headline')
                            ->label('Titular Profesional')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('candidateProfile.phone')
                            ->label('Teléfono de Contacto')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('candidateProfile.city')
                            ->label('Ciudad')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('candidateProfile.province')
                            ->label('Provincia / Estado')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('candidateProfile.summary')
                            ->label('Resumen Profesional')
                            ->placeholder('—')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('candidateProfile.faith_statement')
                            ->label('Declaración de Fe')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ]),
                Infolists\Components\RepeatableEntry::make('candidateProfile.workExperiences')
                    ->label('Trayectoria Profesional / Experiencia')
                    ->schema([
                        Infolists\Components\TextEntry::make('position')
                            ->label('Cargo / Puesto')
                            ->weight('bold'),
                        Infolists\Components\TextEntry::make('company')
                            ->label('Empresa'),
                        Infolists\Components\TextEntry::make('start_date')
                            ->label('Desde')
                            ->date('m/Y'),
                        Infolists\Components\TextEntry::make('end_date')
                            ->label('Hasta')
                            ->date('m/Y')
                            ->placeholder('Presente'),
                        Infolists\Components\TextEntry::make('description')
                            ->label('Descripción de Funciones')
                            ->columnSpanFull()
                            ->placeholder('—'),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->placeholder('No se ha registrado experiencia laboral.'),
                Infolists\Components\RepeatableEntry::make('candidateProfile.educations')
                    ->label('Trayectoria Académica / Educación')
                    ->schema([
                        Infolists\Components\TextEntry::make('degree')
                            ->label('Título / Grado')
                            ->weight('bold'),
                        Infolists\Components\TextEntry::make('institution')
                            ->label('Institución Educativa'),
                        Infolists\Components\TextEntry::make('field_of_study')
                            ->label('Campo de Estudio'),
                        Infolists\Components\TextEntry::make('graduation_year')
                            ->label('Año de Graduación')
                            ->placeholder('En curso'),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->placeholder('No se han registrado estudios académicos.'),
                Infolists\Components\Section::make(__('models/application-note.plural-label'))
                    ->collapsible()
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('notes')
                            ->hiddenLabel()
                            ->schema([
                                Infolists\Components\TextEntry::make('author_name_snapshot')
                                    ->label(__('models/application-note.fields.author')),
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label(__('models/application-note.fields.created_at'))
                                    ->dateTime('d/m/Y H:i'),
                                Infolists\Components\TextEntry::make('body')
                                    ->label(__('models/application-note.fields.body'))
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListApplications::route('/'),
            'view' => Pages\ViewApplication::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
