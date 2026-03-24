<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\JobCategoryResource\Pages;
use App\Helpers\Util;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class JobCategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationGroup = null;

    protected static ?string $slug = 'job-categories';

    public static function getNavigationGroup(): ?string
    {
        return __('models/category.navigation.group');
    }

    public static function getModelLabel(): string
    {
        return __('models/category.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('models/category.plural-label');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('scope', 'JobListing');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(50)
                    ->label(__('models/category.fields.name'))
                    ->placeholder(__('models/category.form.placeholders.name')),
                Forms\Components\TextInput::make('slug')
                    ->maxLength(120)
                    ->label(__('models/category.fields.slug'))
                    ->placeholder(__('models/category.form.placeholders.slug'))
                    ->unique(
                        table: 'categories',
                        column: 'slug',
                        ignoreRecord: true,
                        modifyRuleUsing: fn ($rule) => $rule->where('scope', 'JobListing'),
                    ),
                Forms\Components\TextInput::make('icon')
                    ->maxLength(60)
                    ->label(__('models/category.fields.icon'))
                    ->placeholder(__('models/category.form.placeholders.icon')),
                Forms\Components\TextInput::make('order')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->label(__('models/category.fields.order')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label(__('models/category.fields.name')),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->label(__('models/category.fields.slug')),
                Tables\Columns\IconColumn::make('icon')
                    ->icon(fn (string $state): string => $state)
                    ->label(__('models/category.fields.icon')),
                Tables\Columns\TextColumn::make('order')
                    ->sortable()
                    ->label(__('models/category.fields.order')),
            ])
            ->defaultSort('order')
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label(false)
                    ->mutateFormDataUsing(function (array $data): array {
                        if (empty($data['slug'])) {
                            $data['slug'] = Str::slug($data['name']);
                        }

                        return $data;
                    })
                    ->after(function () {
                        Util::filamentNotification(__('models/category.notifications.updated'));
                    }),
                Tables\Actions\DeleteAction::make()
                    ->label(false)
                    ->after(function () {
                        Util::filamentNotification(__('models/category.notifications.deleted'));
                    }),
            ])
            ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
            ]),
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
            'index' => Pages\ListJobCategories::route('/'),
        ];
    }
}
