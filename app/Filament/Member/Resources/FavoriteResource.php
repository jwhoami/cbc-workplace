<?php

namespace App\Filament\Member\Resources;

use App\Filament\Member\Resources\FavoriteResource\Pages;
use App\Models\Favorite;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FavoriteResource extends Resource
{
    protected static ?string $model = Favorite::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('member_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('rating')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $query->where('member_id', auth('member')->user()?->id);
            })
            ->recordUrl(function (Favorite $record) {
                if ($record->venture->isExpired()) {
                    return null;
                }

                return url()->route('filament.app.resources.ventures.view', [$record->venture]);
            })
            ->columns([
                Tables\Columns\TextColumn::make('venture.title')
                    ->label(__('Emprendimiento'))
                    ->searchable()
                    ->url(function (Favorite $record) {
                        if ($record->venture->isExpired()) {
                            return null;
                        }

                        return url()->route('filament.app.resources.ventures.view', [$record->venture]);
                    }),
                Tables\Columns\TextColumn::make('venture.member.name')
                    ->label(__('Publicado Por'))
                    ->searchable(),
                Tables\Columns\IconColumn::make('venture.is_expired')
                    ->label(__('Activo'))
                    ->boolean()
                    ->alignCenter()
                    ->color(function (Favorite $record) {
                        $isActive = ! ($record->venture->isExpired()) ? true : false;

                        return match ($isActive) {
                            true => 'success',
                            false => 'gray',
                            'default' => 'gray',
                        };
                    })
                    ->icon(function (Favorite $record) {
                        $isActive = ! ($record->venture->isExpired()) ? true : false;

                        return match ($isActive) {
                            true => 'heroicon-o-check-circle',
                            false => 'heroicon-o-check-circle',
                            'default' => 'heroicon-o-check-circle',
                        };
                    }),

                // Tables\Columns\TextColumn::make('rating')
                //   ->numeric()
                //   ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                //        Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->hiddenLabel(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageFavorites::route('/'),
        ];
    }
}
