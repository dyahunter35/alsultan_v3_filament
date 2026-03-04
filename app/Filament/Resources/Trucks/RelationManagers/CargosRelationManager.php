<?php

namespace App\Filament\Resources\Trucks\RelationManagers;

use App\Enums\CargoPriority;
use App\Enums\TruckType;
use App\Filament\Forms\Components\DecimalInput;
use App\Filament\Pages\Concerns\HasRelationManager;
use App\Models\Product;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CargosRelationManager extends RelationManager
{
    use HasRelationManager;

    protected static string $relationship = 'cargos';

    public function form(Schema $schema): Schema
    {
        self::translateConfigureForm();

        return $schema
            ->columns(1)
            ->components([
                    Hidden::make('type')->default(TruckType::Outer->value),

                    Section::make()->columns(2)->schema([
                        Select::make('product_id')
                            ->options(Product::all()->pluck('name', 'id'))
                            ->preload()->searchable()->required(),
                        TextInput::make('size')->default(null),
                    ]),

                    Section::make('الأوزان والكميات')->columns(4)->schema([
                        DecimalInput::make('unit_quantity')
                            ->label('عدد الوحدات')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn($get, $set) => self::refreshCalculations($get, $set)),

                        DecimalInput::make('weight')
                            ->label('وزن الوحدة (جرام)')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn($get, $set) => self::refreshCalculations($get, $set)),

                        DecimalInput::make('ton_weight')
                            ->label('إجمالي الأطنان (يدوي/آلي)')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($get, $set, $state) {
                                // إذا تم تعديل الأطنان يدوياً، نحسب وزن الوحدة (weight)
                                $unitQty = clean_number($get('unit_quantity'));
                                if ($unitQty > 0) {
                                    $newWeight = (clean_number($state) * 1000000) / $unitQty;
                                    $set('weight', round($newWeight, 2));
                                }
                                self::refreshCalculations($get, $set);
                            }),

                        DecimalInput::make('quantity')
                            ->default(0),
                    ]),

                    Section::make('الأسعار')->columns(3)->schema([
                        DecimalInput::make('unit_price')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($get, $set, $state) {
                                $weight = clean_number($get('weight'));
                                if ($weight > 0) {
                                    $tonPrice = (clean_number($state) / $weight) * 1000000;
                                    $set('ton_price', round($tonPrice, 2));
                                }
                                self::refreshCalculations($get, $set);
                            }),

                        DecimalInput::make('ton_price')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($get, $set, $state) {
                                $weight = clean_number($get('weight'));
                                if ($weight > 0) {
                                    $unitPrice = (clean_number($state) * $weight) / 1000000;
                                    $set('unit_price', round($unitPrice, 2));
                                }
                                self::refreshCalculations($get, $set);
                            }),

                        DecimalInput::make('base_total_foreign')
                            ->label('الإجمالي النهائي')
                            ->readonly()
                            ->extraAttributes(['class' => 'font-bold text-primary-600'])
                            ->dehydrated(true),
                    ]),

                    Section::make()->columnSpanFull()->columns(2)->schema([
                        ToggleButtons::make('priority')
                            ->options(CargoPriority::class)
                            ->inline()->grouped()
                            ->live()
                            ->afterStateUpdated(fn($get, $set) => self::refreshCalculations($get, $set))
                            ->default(CargoPriority::Qty->value)
                            ->required(),

                        TextInput::make('note')->default(null),
                    ]),
                ]);
    }

    /**
     * الحسابات المركزية باستخدام helper: clean_number
     */
    protected static function refreshCalculations($get, $set): void
    {
        $unitQty = clean_number($get('unit_quantity'));
        $weight = clean_number($get('weight'));
        $unitPrice = clean_number($get('unit_price'));
        $tonPrice = clean_number($get('ton_price'));
        $tonWeight = clean_number($get('ton_weight')); // نأخذ القيمة الحالية من الحقل
        $priority = $get('priority');

        // تحديث إجمالي الأطنان في حال لم يتم تحديثه في الـ afterStateUpdated
        // (لضمان المزامنة عند تغيير unit_quantity مثلاً)
        if ($unitQty > 0 && $weight > 0 && empty($get('ton_weight'))) {
            $tonWeight = ($unitQty * $weight) / 1000000;
            $set('ton_weight', round($tonWeight, 4));
        }

        // حساب الإجمالي النهائي بناءً على الأولوية
        $isWeightPriority = ($priority instanceof CargoPriority)
            ? $priority === CargoPriority::Weight
            : $priority === CargoPriority::Weight->value;

        if ($isWeightPriority) {
            $total = $tonWeight * $tonPrice;
        } else {
            $total = $unitQty * $unitPrice;
        }

        $set('base_total_foreign', round($total, 2));
    }

    public function table(Table $table): Table
    {
        self::translateConfigureTable();

        return $table
            ->recordTitleAttribute('product_id')
            ->columns([
                    TextColumn::make('product.name')->sortable(),
                    TextColumn::make('unit_quantity')->numeric()->sortable(),
                    TextColumn::make('quantity')->numeric()->sortable()
                    ,
                    TextColumn::make('weight')->numeric()->sortable(),
                    TextColumn::make('unit_price')->numeric()->sortable(),
                    TextColumn::make('ton_price')->numeric()->sortable(),
                    TextColumn::make('ton_weight')->numeric()->sortable()
                        ->summarize(\Filament\Tables\Columns\Summarizers\Sum::make()->label('Total Ton Weight')),
                    TextColumn::make('base_total_foreign')
                        ->label('Total')
                        ->numeric()
                        ->sortable(),
                    TextColumn::make('note')->searchable(),
                ])
            ->headerActions([CreateAction::make()])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([
                    BulkActionGroup::make([DeleteBulkAction::make()]),
                ]);
    }
}