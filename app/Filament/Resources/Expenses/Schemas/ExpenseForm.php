<?php

namespace App\Filament\Resources\Expenses\Schemas;

use App\Enums\ExpansesType as ExpenseType;
use App\Enums\Payment;
use App\Models\Branch;
use App\Models\Expense;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Schemas;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Forms;
use Filament\Support\Icons\Heroicon;

class ExpenseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Schemas\Components\Section::make('المعلومات الأساسية')
                    ->schema([

                        Forms\Components\Select::make('expense_type')
                            ->label('نوع المنصرف')
                            ->options(fn()=>ExpenseType::getGrouped())
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn($set) => $set('amount', 1)),

                        Forms\Components\Select::make('beneficiary_id')
                            ->label('الحساب المستفيد')
                            ->options(fn($get) => self::getFilteredUsers($get('expense_type')))
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('payer_id')
                            ->label('الحساب الدافع')
                            ->options(User::where('blocked', 0)->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('representative_id')
                            ->label('المندوب')
                            ->options(User::role('مندوب')->pluck('name', 'id'))
                            ->visible(fn($get) => $get('expense_type') === ExpenseType::CURRENCY->value)
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns(2),

                Schemas\Components\Section::make('المعلومات المالية')
                    ->schema([
                        Forms\Components\TextInput::make('amount')
                            ->label('الكمية / المبلغ')
                            ->numeric()
                            ->default(1)
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(
                                fn($state, $set, $get) =>
                                $set('total_amount', self::calculateTotal($state, $get('unit_price')))
                            )
                            ->visible(fn($get) => in_array($get('expense_type'), [
                                ExpenseType::CURRENCY->value,
                                ExpenseType::CUSTOMS->value
                            ])),

                        Forms\Components\TextInput::make('unit_price')
                            ->label('سعر الوحدة')
                            ->numeric()
                            ->step(0.01)
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(
                                fn($state, $set, $get) =>
                                $set('total_amount', self::calculateTotal($get('amount'), $state))
                            ),

                        Forms\Components\TextInput::make('total_amount')
                            ->label('الإجمالي')
                            ->numeric()
                            ->readOnly()
                            ->dehydrated(true),

                        Forms\Components\TextInput::make('remaining_amount')
                            ->label('المبلغ المتبقي')
                            ->numeric()
                            ->default(0)
                            ->visible(fn($get) => !$get('is_paid')),
                    ])
                    ->columns(2),

                Schemas\Components\Section::make('المعلومات الإضافية')
                    ->schema([
                        Forms\Components\Select::make('store_id')
                            ->label('المخزن')
                            ->options(Branch::pluck('name', 'id'))
                            ->visible(fn($get) => in_array($get('expense_type'), [
                                ExpenseType::STORE_TRAN->value,
                                ExpenseType::STORE_FOOD->value,
                                ExpenseType::STORE_RENT->value,
                                ExpenseType::STORE_CARRIER->value,
                            ]))
                            ->searchable()
                            ->preload(),

                        Forms\Components\TextInput::make('payment_reference')
                            ->label('رقم الإشعار / المرجع')
                            ->maxLength(255),

                        Forms\Components\Select::make('payment_method')
                            ->label('وسيلة الدفع')
                            ->options(Payment::class)
                            ->required(),

                        Forms\Components\Toggle::make('is_paid')
                            ->label('حالة الدفع')
                            ->default(false)
                            ->inline(false)
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set) {
                                if ($state) {
                                    $set('remaining_amount', 0);
                                }
                            })
                            ->onColor('success')
                            ->offColor('warning')
                            ->onIcon('heroicon-s-check')
                            ->offIcon('heroicon-s-clock')
                            ->label(fn($state) => $state ? 'مدفوع' : 'مؤجل'),

                        Forms\Components\Textarea::make('notes')
                            ->label('ملاحظات')
                            ->columnSpanFull(),

                        Forms\Components\DateTimePicker::make('created_at')
                            ->label('التاريخ والوقت')
                            ->default(now())
                            ->required(),
                    ])
                    ->columns(2)
            ]);
    }

    private static function getFilteredUsers(?ExpenseType $expenseType): array
    {
        if (!$expenseType) {
            return User::where('blocked', 0)->pluck('name', 'id')->toArray();
        }
        return match ($expenseType) {
            ExpenseType::CURRENCY => User::where('permanent', ExpenseType::CURRENCY->value)->pluck('name', 'id')->toArray(),
            ExpenseType::CUSTOMS => User::where('permanent', ExpenseType::CUSTOMS->value)->pluck('name', 'id')->toArray(),
            ExpenseType::GOVERMENT => User::where('permanent', ExpenseType::GOVERMENT->value)->pluck('name', 'id')->toArray(),
            ExpenseType::TAX => User::where('permanent', ExpenseType::TAX->value)->pluck('name', 'id')->toArray(),
            default => User::where('blocked', 0)->pluck('name', 'id')->toArray()
        };
    }

    private static function calculateTotal($amount, $unitPrice): float
    {
        $amount = floatval($amount ?? 1);
        $unitPrice = floatval($unitPrice ?? 1);

        return $amount * $unitPrice;
    }

    private static function formatAmount($amount): string
    {
        return number_format($amount * 1000000);
    }
}
