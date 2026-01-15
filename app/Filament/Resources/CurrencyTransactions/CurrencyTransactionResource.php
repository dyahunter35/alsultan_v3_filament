<?php

namespace App\Filament\Resources\CurrencyTransactions;

use App\Enums\CurrencyType;
use App\Enums\ExpenseGroup;
use App\Filament\Forms\Components\DecimalInput;
use App\Filament\Forms\Components\MorphSelect;
use App\Filament\Pages\Concerns\HasResource;
use App\Filament\Resources\CurrencyTransactions\Pages\ManageCurrencyTransactions;
use App\Models\Company;
use App\Models\CurrencyBalance;
use App\Models\CurrencyTransaction;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CurrencyTransactionResource extends Resource
{
    use HasResource;

    protected static ?string $model = CurrencyTransaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ArrowTopRightOnSquare;

    protected static ?int $navigationSort = 13;

    public static function form(Schema $schema): Schema
    {
        self::translateConfigureForm();

        return $schema
            ->components(self::formSchema());
    }

    public static function table(Table $table): Table
    {
        self::translateConfigureTable();

        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                    TextColumn::make('created_at')
                        ->date('d-m-Y')
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: false),
                    TextColumn::make('currency.name')
                        ->badge()
                        ->sortable(),

                    TextColumn::make('payer.name')
                        ->searchable(),

                    TextColumn::make('party.name')
                        ->searchable(),

                    TextColumn::make('amount')
                        ->numeric()
                        ->sortable(),

                    TextColumn::make('rate')
                        ->numeric()
                        ->sortable(),

                    TextColumn::make('total')
                        ->numeric()
                        ->sortable(),

                    TextColumn::make('type')
                        ->badge(),

                    TextColumn::make('note')
                        ->searchable(),

                    TextColumn::make('updated_at')
                        ->dateTime()
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),
                    TextColumn::make('deleted_at')
                        ->dateTime()
                        ->sortable()
                        //->visible(auth()->user()->hasPermissionTo(''))
                        ->toggleable(isToggledHiddenByDefault: true),
                ])
            ->filters([
                    TrashedFilter::make(),
                    SelectFilter::make('type')
                        ->options(CurrencyType::class),
                ])
            ->recordActions([
                    EditAction::make(),
                    DeleteAction::make(),
                    ForceDeleteAction::make(),
                    RestoreAction::make(),
                ])
            ->toolbarActions([
                    BulkActionGroup::make([
                        DeleteBulkAction::make(),
                        ForceDeleteBulkAction::make(),
                        RestoreBulkAction::make(),
                    ]),
                ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCurrencyTransactions::route('/'),
        ];
    }

    public static function formSchema($type = CurrencyType::Convert): array
    {
        return [
            Grid::make(2)
                ->columnSpanFull()
                ->schema([
                        Section::make('بيانات المستفيد')
                            ->columns(2)
                            ->schema([
                                    Select::make('type')
                                        ->options(\App\Enums\CurrencyType::class)
                                        ->default($type)
                                        ->live()
                                        ->columnSpanFull()
                                        ->afterStateUpdated(function (callable $set, $state) {
                                            $set('amount', 0);
                                            $set('rate', 0);
                                            $set('total', 0);
                                        })
                                        ->required(),

                                    ViewField::make('payer_currencies')
                                        ->label('Currencies')
                                        ->view('filament.resources.customers.forms.customer-currencies-table')
                                        ->reactive()
                                        ->hidden(fn(callable $get) => empty($get('payer_currencies')))
                                        ->columnSpanFull(),

                                    Select::make('payer_id')
                                        ->options(

                                            \App\Models\Customer::select('name', 'id', 'balance')->where('permanent', ExpenseGroup::DEBTORS->value)->get()
                                                ->mapWithKeys(fn(\App\Models\Customer $customer) => [
                                                    $customer->id => sprintf(
                                                        '%s (%s SDG)',
                                                        $customer->name,
                                                        number_format($customer->balance, 2)
                                                    ),
                                                ])

                                        )
                                        ->reactive()
                                        ->afterStateUpdated(function (callable $set, $state) {
                                            $currencies = CurrencyBalance::where('owner_type', \App\Models\Customer::class)
                                                ->where('owner_id', $state)
                                                ->get();

                                            $set('payer_currencies', $currencies);
                                        }),

                                    Hidden::make('payer_type')
                                        ->default(\App\Models\Customer::class)
                                        ->required(),

                                    MorphSelect::make('party')
                                        ->models([
                                                'company' => \App\Models\Company::class,
                                                'customer' => fn() => \App\Models\Customer::where('permanent', ExpenseGroup::DEBTORS->value)->get(),
                                            ])
                                        ->live()
                                        ->hidden(fn(callable $get) => in_array($get('type'), [CurrencyType::Convert])),

                                    Hidden::make('party_type')
                                        ->hidden(fn(callable $get) => in_array($get('type'), [CurrencyType::Convert])),

                                    Hidden::make('party_id')
                                        ->hidden(condition: fn(callable $get) => in_array($get('type'), [CurrencyType::Convert])),

                                    Select::make('currency_id')
                                        ->relationship('currency', 'name')
                                        ->required(),

                                    TextInput::make('note')
                                        ->default(null)->columnSpanFull(),
                                ]),

                        // ... داخل formSchema ...
                        Grid::make(1)->schema([
                            Section::make('أداة التحويل السريع (للحساب فقط)')
                                ->schema([
                                        Grid::make(2)->schema([
                                            DecimalInput::make('converter_rate')
                                                ->label('سعر الصرف (للأداة)')
                                                ->live(),

                                            DecimalInput::make('converter_amount')
                                                ->label('المبلغ المراد تحويله')
                                                ->million()
                                                ->placeholder('مثلاً: 1.5') // المستخدم يدخل القيمة مقسومة كما يراها دائماً
                                            ,
                                        ]),

                                        Radio::make('conversion_direction')
                                            ->options([
                                                    'to_sdg' => 'من أجنبي إلى سوداني',
                                                    'from_sdg' => 'من سوداني إلى أجنبي',
                                                ])
                                            ->default('to_sdg')
                                            ->inline()
                                            ->live(),

                                        Actions::make([
                                            Action::make('apply_conversion')
                                                ->label('تطبيق الحساب')
                                                ->color('warning')
                                                ->icon('heroicon-m-calculator')
                                                ->action(function ($set, $get) {
                                                    // تحويل النصوص إلى أرقام نقية لإجراء العمليات الحسابية
                                                    $rate = (float) str_replace(',', '', $get('converter_rate') ?? 0);
                                                    $inputAmount = (float) str_replace(',', '', $get('converter_amount') ?? 0);

                                                    if ($get('conversion_direction') === 'to_sdg') {
                                                        // الحالة: أدخلنا مبلغاً أجنبياً ونريد الإجمالي بالسوداني
                                                        $set('amount', $inputAmount);
                                                        $set('rate', $rate);
                                                        $set('total', $inputAmount * $rate);
                                                    } else {
                                                        // الحالة: أدخلنا مبلغاً سودانياً ونريد معرفة كم يساوي بالأجنبي
                                                        $foreignAmount = $rate > 0 ? ($inputAmount / $rate) : 0;
                                                        $set('amount', $foreignAmount);
                                                        $set('rate', $rate);
                                                        $set('total', $inputAmount);
                                                    }
                                                })
                                        ])->fullWidth(),
                                    ])
                                ->collapsible(),

                            Section::make('بيانات العملية النهائية (للحفظ)')
                                ->schema([
                                        Grid::make(2)->schema([
                                            DecimalInput::make('amount')
                                                ->label('المبلغ بالأجنبي')
                                                ->required()
                                                ->million() // سيقوم بضرب القيمة في مليون عند الحفظ تلقائياً
                                                ->live(onBlur: true)
                                                ->afterStateUpdated(fn($set, $get) => self::updateTotal($set, $get)),

                                            DecimalInput::make('rate')
                                                ->label('سعر الصرف النهائي')
                                                ->required()
                                                ->default(1)
                                                ->live(onBlur: true)
                                                ->afterStateUpdated(fn($set, $get) => self::updateTotal($set, $get)),
                                        ]),
                                        DecimalInput::make('total')
                                            ->label('المجموع (بالسوداني)')
                                            ->required()
                                            ->million() // سيقوم بضرب القيمة في مليون عند الحفظ تلقائياً
                                            ->readOnly()
                                            ->extraAttributes(['class' => 'bg-slate-50 font-bold']),
                                    ]),

                        ]),

                    ]),

        ];
    }
    protected static function updateTotal($set, $get)
    {
        // تنظيف القيم من الفواصل قبل الحساب لضمان الدقة
        $amount = (float) str_replace(',', '', $get('amount') ?? 0);
        $rate = (float) str_replace(',', '', $get('rate') ?? 0);

        $set('total', $amount * $rate);
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                    SoftDeletingScope::class,
                ]);
    }
}
