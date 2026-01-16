<?php

namespace App\Filament\Resources\SalaryPayments;

use App\Models\SalaryAdvance;
use App\Models\SalaryPayment;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Schemas;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class SalaryPaymentResource extends Resource
{
    protected static ?string $model = SalaryPayment::class;
    protected static bool $shouldRegisterNavigation = false;

    // protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    // protected static ?string $navigationGroup = 'الرواتب';
    // protected static ?int $navigationSort = 2;

    public static function getModelLabel(): string
    {
        return 'مسير راتب';
    }

    public static function getPluralModelLabel(): string
    {
        return 'مسيرات الرواتب';
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                    Schemas\Components\Section::make('بيانات الموظف والشهر')
                        ->schema([
                                Forms\Components\Select::make('employee_id')
                                    ->label('الموظف')
                                    ->options(User::role('employee')->pluck('name', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, $state) {
                                        if ($state) {
                                            $user = User::find($state);
                                            if ($user) {
                                                $set('base_salary', $user->base_salary ?? 0);
                                                $set('hourly_rate', $user->hourly_rate ?? 0);

                                                // Calculate unrecovered advances
                                                $advances = SalaryAdvance::where('employee_id', $state)
                                                    ->where('is_recovered', false)
                                                    ->sum('amount');
                                                $set('advances_deducted', $advances);
                                            }
                                        }
                                    }),

                                Forms\Components\DatePicker::make('payment_date')
                                    ->label('تاريخ الدفع')
                                    ->default(now())
                                    ->required(),

                                Forms\Components\TextInput::make('for_month')
                                    ->label('عن شهر (YYYY-MM)')
                                    ->default(now()->format('Y-m'))
                                    ->required()
                                    ->placeholder('YYYY-MM')
                                    ->regex('/^\d{4}-\d{2}$/'),
                            ])->columns(3),

                    Schemas\Components\Section::make('تفاصيل الراتب')
                        ->schema([
                                Forms\Components\TextInput::make('base_salary')
                                    ->label('الراتب الأساسي')
                                    ->numeric()
                                    ->default(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn(Get $get, Set $set) => self::calculateNetPay($get, $set)),

                                Forms\Components\TextInput::make('transportation_allowance')
                                    ->label('بدل نقل')
                                    ->numeric()
                                    ->default(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn(Get $get, Set $set) => self::calculateNetPay($get, $set)),

                                Forms\Components\TextInput::make('housing_allowance')
                                    ->label('بدل سكن')
                                    ->numeric()
                                    ->default(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn(Get $get, Set $set) => self::calculateNetPay($get, $set)),
                            ])->columns(3),

                    Schemas\Components\Section::make('ساعات العمل (اختياري)')
                        ->schema([
                                Forms\Components\Toggle::make('calculate_by_hours')
                                    ->label('حساب بالساعة')
                                    ->live()
                                    ->dehydrated(false)
                                    ->afterStateUpdated(fn(Get $get, Set $set) => self::calculateNetPay($get, $set)),

                                Forms\Components\TextInput::make('work_hours')
                                    ->label('عدد الساعات')
                                    ->numeric()
                                    ->visible(fn(Get $get) => $get('calculate_by_hours'))
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn(Get $get, Set $set) => self::calculateNetPay($get, $set)),

                                Forms\Components\TextInput::make('hourly_rate')
                                    ->label('سعر الساعة')
                                    ->numeric()
                                    ->visible(fn(Get $get) => $get('calculate_by_hours'))
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn(Get $get, Set $set) => self::calculateNetPay($get, $set)),
                            ])->columns(3),

                    Schemas\Components\Section::make('الاستقطاعات والإضافات')
                        ->schema([
                                Forms\Components\TextInput::make('advances_deducted')
                                    ->label('خصم سلفيات')
                                    ->numeric()
                                    ->default(0)
                                    ->live(onBlur: true)
                                    ->helperText('يتم تحديده تلقائياً بناءً على السلف غير المحصلة')
                                    ->afterStateUpdated(fn(Get $get, Set $set) => self::calculateNetPay($get, $set)),

                                Forms\Components\TextInput::make('penalties')
                                    ->label('خصومات/جزاءات')
                                    ->numeric()
                                    ->default(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn(Get $get, Set $set) => self::calculateNetPay($get, $set)),

                                Forms\Components\TextInput::make('incentives')
                                    ->label('حوافز/مكافآت')
                                    ->numeric()
                                    ->default(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn(Get $get, Set $set) => self::calculateNetPay($get, $set)),
                            ])->columns(3),

                    Schemas\Components\Section::make('صافي الراتب')
                        ->schema([
                                Forms\Components\TextInput::make('net_pay')
                                    ->label('صافي الراتب المستحق')
                                    ->disabled()
                                    ->dehydrated()
                                    ->required()
                                    ->numeric()
                                    ->prefix('ر.س')
                                    ->columnSpanFull()
                                    ->extraInputAttributes(['style' => 'font-size: 1.5rem; font-weight: bold; text-align: center;']),

                                Forms\Components\Textarea::make('notes')
                                    ->label('ملاحظات')
                                    ->columnSpanFull(),

                                Forms\Components\Hidden::make('payer_id')
                                    ->default(fn() => Auth::id()),
                            ]),
                ]);
    }

    public static function calculateNetPay(Get $get, Set $set): void
    {
        $baseSalary = (float) $get('base_salary');
        $transport = (float) $get('transportation_allowance');
        $housing = (float) $get('housing_allowance');

        $isHourly = (bool) $get('calculate_by_hours');
        $workHours = (float) $get('work_hours');
        $hourlyRate = (float) $get('hourly_rate');

        $advances = (float) $get('advances_deducted');
        $penalties = (float) $get('penalties');
        $incentives = (float) $get('incentives');

        $gross = 0;

        if ($isHourly) {
            $gross = ($workHours * $hourlyRate) + $incentives;
        } else {
            $gross = $baseSalary + $transport + $housing + $incentives;
        }

        $net = $gross - ($advances + $penalties);

        $set('net_pay', max(0, $net));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                    Tables\Columns\TextColumn::make('payment_date')
                        ->label('Tarix')
                        ->date()
                        ->sortable(),
                    Tables\Columns\TextColumn::make('employee.name')
                        ->label('الموظف')
                        ->searchable(),
                    Tables\Columns\TextColumn::make('for_month')
                        ->label('عن شهر'),
                    Tables\Columns\TextColumn::make('net_pay')
                        ->label('صافي الراتب')
                        ->money('SAR')
                        ->sortable(),
                    Tables\Columns\TextColumn::make('payer.name')
                        ->label('Admin')
                        ->toggleable(isToggledHiddenByDefault: true),
                ])
            ->filters([
                    //
                ])
            ->actions([
                    EditAction::make(),
                ])
            ->bulkActions([
                    BulkActionGroup::make([
                        DeleteBulkAction::make(),
                    ]),
                ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageSalaryPayments::route('/'),
            //'create' => Pages\CreateSalaryPayment::route('/create'),
            //'edit' => Pages\EditSalaryPayment::route('/{record}/edit'),
        ];
    }
}
