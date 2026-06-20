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
        return __('navigation.bolsa-de-trabajo');
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
                Forms\Components\Select::make('icon')
                    ->label(__('models/category.fields.icon'))
                    ->placeholder(__('models/category.form.placeholders.icon'))
                    ->options([
                        // Iconos del Seeder
                        'heroicon-o-briefcase' => '💼 Maletín / Empleo General',
                        'heroicon-o-calculator' => '🧮 Administración y Finanzas (Calculadora)',
                        'heroicon-o-computer-desktop' => '💻 Tecnología e Informática (Computadora)',
                        'heroicon-o-academic-cap' => '🎓 Educación y Docencia (Gorra de Graduación)',
                        'heroicon-o-heart' => '❤️ Pastoral y Ministerio (Corazón)',
                        'heroicon-o-megaphone' => '📣 Comunicación y Medios (Megáfono)',
                        'heroicon-o-shield-check' => '🛡️ Salud y Bienestar (Escudo)',
                        'heroicon-o-wrench-screwdriver' => '🛠️ Servicios Generales (Herramientas)',
                        'heroicon-o-paint-brush' => '🎨 Diseño y Creatividad (Pincel)',
                        'heroicon-o-hand-raised' => '🙋 Voluntariado (Mano Alzada)',

                        // Iconos complementarios muy útiles
                        'heroicon-o-chart-bar' => '📊 Ventas y Marketing (Gráfico de Barras)',
                        'heroicon-o-currency-dollar' => '💵 Finanzas / Ventas (Dólar)',
                        'heroicon-o-user' => '👤 Recursos Humanos / Personal',
                        'heroicon-o-users' => '👥 Equipo / Comunidad',
                        'heroicon-o-globe-alt' => '🌐 Internacional / Web',
                        'heroicon-o-home' => '🏠 Inmobiliaria / Mantenimiento',
                        'heroicon-o-truck' => '🚚 Logística y Transporte (Camión)',
                        'heroicon-o-shopping-bag' => '🛍️ Comercio / Tiendas',
                        'heroicon-o-book-open' => '📖 Lectura / Escritura (Libro Abierto)',
                        'heroicon-o-camera' => '📷 Fotografía y Video (Cámara)',
                        'heroicon-o-cog' => '⚙️ Operaciones / Ajustes (Engranaje)',
                        'heroicon-o-phone' => '📞 Soporte / Atención Telefónica',
                        'heroicon-o-map-pin' => '📍 Ubicación / Geografía (Pin)',
                        'heroicon-o-scale' => '⚖️ Legal y Leyes (Balanza)',
                        'heroicon-o-star' => '⭐ Destacado / Calidad (Estrella)',
                    ])
                    ->searchable()
                    ->reactive()
                    ->prefixIcon(fn ($state) => empty($state) ? 'heroicon-o-question-mark-circle' : $state)
                    ->default('heroicon-o-briefcase'),
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
                    ->icon(function (?string $state): ?string {
                        if (empty($state)) {
                            return 'heroicon-o-question-mark-circle';
                        }
                        try {
                            app(\BladeUI\Icons\Factory::class)->svg($state);
                            return $state;
                        } catch (\Throwable $e) {
                            return 'heroicon-o-question-mark-circle';
                        }
                    })
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
