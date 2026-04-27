<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-chevron-right';

    protected static ?string $navigationGroup = 'Sistema';

    protected static ?string $modelLabel = 'Usuario';

    protected static ?string $pluralModelLabel = 'Usuarios';

    public static function form(Form $form): Form
    {
        return $form
            ->columns(2)
            ->schema([
                Forms\Components\TextInput::make('username')
                    ->label(__('Usuario'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('name')
                    ->label(__('Nombre'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->label(__('Email'))
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('role_id')
                    ->label(__('Role'))
                    ->relationship('role', 'name'),
                Forms\Components\TextInput::make('password')
                    ->label(__('Contraseña'))
                    ->password()
                    ->required()
                    ->maxLength(255)
                    ->same('password_confirmation')
                    ->hiddenOn(['edit', 'view'])
                    ->dehydrateStateUsing(fn (string $state): string => Hash::make($state)),
                Forms\Components\TextInput::make('password_confirmation')
                    ->label(__('Confirmar Contraseña'))
                    ->password()
                    ->required()
                    ->maxLength(255)
                    ->hiddenOn(['edit', 'view']),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label(__('Id'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('username')
                    ->label(__('Usuario'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Nombre'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('Email'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('role.name')
                    ->label(__('Role')),
                Tables\Columns\IconColumn::make('can_approve')
                    ->label(__('Aprobador'))
                    ->boolean()
                    ->alignCenter()
                    ->action(function (User $record): void {
                        abort_if(! auth('admin')->user()->hasPermission('user.toggleflag-can_approve'), 401);
                        $record->can_approve = ! $record->can_approve;
                        $record->save();
                    }),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('Activo'))
                    ->boolean()
                    ->alignCenter()
                    ->action(function (User $record): void {
                        abort_if(! auth('admin')->user()->hasPermission('user.toggleflag-active'), 401);
                        $record->is_active = ! $record->is_active;
                        $record->save();
                    }),
                Tables\Columns\IconColumn::make('is_blocked')
                    ->label(__('Bloqueado'))
                    ->boolean()
                    ->alignCenter()
                    ->action(function (User $record): void {
                        abort_if(! auth('admin')->user()->hasPermission('user.toggleflag-blocked'), 401);
                        $record->is_blocked = ! $record->is_blocked;
                        $record->save();
                    }),
            ])
            ->filters([
            Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('Activo')),
            Tables\Filters\TernaryFilter::make('is_blocked')
                    ->label(__('Bloqueado')),
        ])
            ->actions([
            Tables\Actions\EditAction::make()
                    ->label(false)
                    ->toolTip(__('Editar')),
            Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('setPassword')
                        ->icon('heroicon-o-key')
                        ->label('Fijar Contraseña')
                        ->modalWidth('sm')
                        ->action(function (User $record, array $data): void {
                            abort_if(! auth('admin')->user()->hasPermission($record, 'user.set-password'), 401);
                            $record->password = $data['password'];
                            $record->save();
                            Notification::make()->title(__('Operación Exitosa'))->success()->send();
                        })
                        ->visible(fn (User $record): bool => auth('admin')->user()->hasPermission($record, 'user.set-password'))
                        ->form([
                            TextInput::make('password')
                                ->label('Contraseña')
                                ->password()
                                ->revealable()
                                ->rule(Password::default())
                                ->autocomplete('off')
                                ->dehydrated(fn ($state): bool => filled($state))
                                ->dehydrateStateUsing(fn ($state): string => Hash::make($state))
                                ->same('passwordConfirmation'),
                            TextInput::make('passwordConfirmation')
                                ->label('Confirmar Contraseña')
                                ->password()
                                ->revealable()
                                ->required()
                                ->dehydrated(false),
                        ]),
                    Tables\Actions\DeleteAction::make()
                        ->label(__('Eliminar')),
            ]),
        ])
            ->bulkActions([
            Tables\Actions\DeleteBulkAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageUsers::route('/'),
        ];
    }
}
