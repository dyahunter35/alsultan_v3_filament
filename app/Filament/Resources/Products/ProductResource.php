<?php

namespace App\Filament\Resources\Products;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Products\Pages\SingleStockReport;
use App\Filament\Resources\Products\RelationManagers\HistoryRelationManager;
use App\Models\Product;
use App\Models\Scopes\IsVisibleScope;
use Filament\Actions\Action;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\BooleanConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\NumberConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    use \App\Filament\Pages\Concerns\HasResource;

    protected static ?string $model = Product::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 7;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-bolt';

    protected static bool $isScopedToTenant = false;

    // --- NAVIGATION ---
    public static function getModelLabel(): string
    {
        return __('product.navigation.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('product.navigation.plural_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('product.navigation.label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('product.navigation.group');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScope(IsVisibleScope::class);
    }

    // --- FORM ---
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->schema([
                        Section::make()
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('product.fields.name.label'))
                                    ->placeholder(__('product.fields.name.placeholder'))
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (string $operation, $state, Set $set) => $set('slug', Str::slug($state))),

                                TextInput::make('slug')
                                    ->label(__('product.fields.slug.label'))
                                    ->disabled()
                                    ->dehydrated()
                                    ->required()
                                    ->unique(Product::class, 'slug', ignoreRecord: true),

                                MarkdownEditor::make('description')
                                    ->label(__('product.fields.description.label'))
                                    ->placeholder(__('product.fields.description.placeholder'))
                                    ->columnSpan('full'),
                            ])
                            ->columns(2)
                            ->columnSpan(['lg' => 2]),

                        Grid::make(2)
                            ->schema([
                                Section::make(__('product.sections.pricing.label'))
                                    ->schema([
                                        TextInput::make('price')
                                            ->label(__('product.fields.price.label'))
                                            ->numeric()
                                            ->required(),

                                    ])
                                    ->columnSpan(1),
                                Section::make(__('product.sections.inventory.label'))
                                    ->schema([
                                        TextInput::make('security_stock')
                                            ->label(__('product.fields.security_stock.label'))
                                            ->helperText(__('product.fields.security_stock.helper'))
                                            ->numeric()
                                            ->rules(['integer', 'min:0'])
                                            ->required(),

                                    ])
                                    ->columnSpan(1),
                            ])->columnSpan(2),

                        /* Forms\Components\Section::make(__('product.sections.shipping.label'))
                            ->schema([
                                Forms\Components\Checkbox::make('backorder')
                                    ->label(__('product.fields.backorder.label')),

                                Forms\Components\Checkbox::make('requires_shipping')
                                    ->label(__('product.fields.requires_shipping.label')),
                            ])
                            ->columns(2), */

                    ])
                    ->columns(2)
                    ->columnSpan(['lg' => 2]),

                Group::make()
                    ->schema([
                        Section::make(__('product.sections.images.label'))
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('media')
                                    ->collection('product-images')
                                    ->multiple()
                                    ->maxFiles(5)
                                    ->hiddenLabel(),
                            ])
                            ->collapsible(),

                        Section::make(__('product.sections.status.label'))
                            ->schema([
                                Toggle::make('is_visible')
                                    ->label(__('product.fields.is_visible.label'))
                                    ->helperText(__('product.fields.is_visible.helper'))
                                    ->default(true),

                                /* DatePicker::make('published_at')
                                    ->label(__('product.fields.published_at.label'))
                                    ->default(now())
                                    ->required(), */
                            ]),

                        Section::make(__('product.sections.associations.label'))
                            ->schema([
                                Select::make('branches')
                                    ->label(__('product.fields.branch.label'))
                                    ->placeholder(__('product.fields.branch.placeholder'))
                                    ->relationship('branches', 'name')
                                    ->multiple()
                                    ->preload()
                                    ->searchable(),

                                Select::make('category_id')
                                    ->label(__('product.fields.category.label'))
                                    ->placeholder(__('product.fields.category.placeholder'))
                                    ->relationship('category', 'name')
                                    ->preload()
                                    ->required(),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    // --- TABLE ---
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('product-image')
                    ->label(__('product.columns.image.label'))
                    ->collection('product-images'),

                TextColumn::make('name')
                    ->label(__('product.columns.name.label'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category.name')
                    ->label(__('product.columns.category.label'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                IconColumn::make('is_visible')
                    ->label(__('product.columns.visibility.label'))
                    ->sortable()
                    ->boolean(),

                TextColumn::make('price')
                    ->label(__('product.columns.price.label'))
                    ->searchable()
                    ->sortable(),

                /* Tables\Columns\TextColumn::make('sku')
                    ->label(__('product.columns.sku.label'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(), */

                TextColumn::make('security_stock')
                    ->label(__('product.columns.security_stock.label'))
                    ->searchable()
                    ->sortable()
                    // ->visible(fn() => !auth()->user()->hasRole('بائع'))
                    ->toggleable(),

                TextColumn::make('stock_for_current_branch')
                    ->label(__('product.columns.quantity.label'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('total_stock')
                    ->label(__('product.columns.all_branches_quantity.label'))
                    ->searchable()
                    ->sortable()
                    ->color(fn ($record) => $record->total_stock > $record->security_stock ? 'success' : 'danger')
                    ->visible(fn () => ! auth()->user()->hasRole('بائع'))
                    ->toggleable(),

                TextColumn::make('branches.name')
                    ->label(__('product.columns.branch.label'))
                    ->searchable()
                    ->badge()
                    ->visible(fn () => ! auth()->user()->hasRole('بائع'))
                    ->sortable(),

                TextColumn::make('published_at')
                    ->label(__('product.columns.publish_date.label'))
                    ->date()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
            ])
            ->filters([
                QueryBuilder::make()
                    ->constraints([
                        TextConstraint::make('name')->label(__('product.filters.constraints.name')),
                        TextConstraint::make('slug')->label(__('product.filters.constraints.slug')),
                        // TextConstraint::make('sku')->label(__('product.filters.constraints.sku')),
                        // TextConstraint::make('barcode')->label(__('product.filters.constraints.barcode')),
                        TextConstraint::make('description')->label(__('product.filters.constraints.description')),
                        // NumberConstraint::make('old_price')->label(__('product.filters.constraints.old_price')),
                        NumberConstraint::make('price')->label(__('product.filters.constraints.price')),
                        // NumberConstraint::make('cost')->label(__('product.filters.constraints.cost')),
                        NumberConstraint::make('security_stock')->label(__('product.filters.constraints.security_stock')),
                        BooleanConstraint::make('is_visible')->label(__('product.filters.constraints.is_visible')),
                        // BooleanConstraint::make('featured')->label(__('product.filters.constraints.featured')),
                        // BooleanConstraint::make('backorder')->label(__('product.filters.constraints.backorder')),
                        // BooleanConstraint::make('requires_shipping')->label(__('product.filters.constraints.requires_shipping')),
                        // DateConstraint::make('published_at')->label(__('product.filters.constraints.published_at')),
                    ])
                    ->constraintPickerColumns(2),
            ], layout: FiltersLayout::Modal)
            ->deferFilters()
            ->recordActions([
                EditAction::make(),
                Action::make('stock')
                    ->label(__('product.actions.stock.label'))
                    ->icon('heroicon-o-chart-bar')
                    ->url(fn (Product $record) => ProductResource::getUrl('stock', ['record' => $record]))
                    ->openUrlInNewTab()
                    ->color('secondary'),
            ])
            ->groupedBulkActions([
                DeleteBulkAction::make()
                    ->action(function () {
                        Notification::make()
                            ->title(__('product.actions.delete.notification'))
                            ->warning()
                            ->send();
                    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            HistoryRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
            'stock' => SingleStockReport::route('/{record}/stock-report'),
        ];
    }
}
