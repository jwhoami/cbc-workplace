<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TextResource\Pages;
use App\Helpers\Util;
use App\Models\Config;
use App\Models\Text;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TextResource extends Resource
{
    protected static ?string $model = Text::class;

    protected static ?string $navigationIcon = 'heroicon-o-chevron-right';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationGroup = 'Administración';

    //  public static function getNavigationGroup(): ?string
    //  {
    //    return __('Administración');
    //  }

    public static function getModelLabel(): string
    {
        return __('Texto');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Textos');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Grid::make()
                    ->columns(3)
                    ->schema([
                        Infolists\Components\Section::make(__('Contenido'))
                            ->columnSpanFull()
                            ->schema([
                                Infolists\Components\TextEntry::make('type')
                                    ->label(__('Tipo')),
                                Infolists\Components\TextEntry::make('code')
                                    ->label(__('Código')),
                                Infolists\Components\TextEntry::make('title')
                                    ->label(__('Título')),
                                Infolists\Components\TextEntry::make('content')
                                    ->label(__('Contenido'))
                                    ->html(),
                            ]),
                    ]),
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label(__('Tipo'))
                            ->options(Config::make()->getp('textTypes', [])),
                        Forms\Components\TextInput::make('code')
                            ->label(__('Código'))
                            ->maxLength(255)
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->hiddenOn(['create']),
                        Forms\Components\TextInput::make('title')
                            ->label(__('Título'))
                            ->maxLength(255)
                            ->required(),
                        Forms\Components\RichEditor::make('content')
                            ->columnSpanFull()
                            ->label(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label(__('Id')),
                Tables\Columns\TextColumn::make('code')
                    ->label(__('Código')),
                Tables\Columns\TextColumn::make('title')
                    ->label(__('Título'))
                    ->limit(90),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('Tipo')),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('Activo'))
                    ->boolean()
                    ->action(function (Text $record): void {
                        $record->is_active = ! $record->is_active;
                        $record->save();
                        Util::filamentNotification('!OPERATION-SUCCESS');
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label(false)
                    ->tooltip(__('common.actions.edit.tooltip')),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('edit-code')
                        ->label(__('Editar HTML'))
                        ->icon('heroicon-o-chevron-right')
                        ->fillForm(function (Text $record) {
                            return [
                                'html' => $record->content,
                            ];
                        })
                        ->form([
                            Forms\Components\Textarea::make('html')
                                ->label(__('HTML'))
                                ->rows(10),
                        ])
                        ->action(function (Text $record, array $data) {
                            $record->content = $data['html'];
                            $record->save();
                            Util::filamentNotification('!OPERATION-SUCCESS');
                        }),
                    Tables\Actions\ViewAction::make()
                        ->label(__('common.actions.view.label'))
                        ->tooltip(__('common.actions.view.tooltip')),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTexts::route('/'),
            'create' => Pages\CreateText::route('/create'),
            'view' => Pages\ViewText::route('/{record}'),
            'edit' => Pages\EditText::route('/{record}/edit'),
        ];
    }
}
