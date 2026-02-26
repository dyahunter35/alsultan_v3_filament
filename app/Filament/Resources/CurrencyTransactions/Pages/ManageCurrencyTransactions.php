<?php

namespace App\Filament\Resources\CurrencyTransactions\Pages;

use App\Enums\CurrencyType;
use App\Filament\Resources\CurrencyTransactions\CurrencyTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageCurrencyTransactions extends ManageRecords
{
    protected static string $resource = CurrencyTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // 1. زر تحويل عملة (Convert)
            Actions\CreateAction::make('convert')
                ->label('تحويل عملة (داخلي)')
                ->modalHeading('إجراء عملية تحويل عملة لعميل')
                ->icon('heroicon-m-arrows-right-left')
                ->color('success')
                ->form(CurrencyTransactionResource::formSchema(CurrencyType::Convert))
                ->mutateFormDataUsing(function (array $data): array {
                    $data['type'] = CurrencyType::Convert;
                    return $data;
                }),

            // 2. زر منصرفات شركة (Company Expense)
            Actions\CreateAction::make('company_expense')
                ->label('دفع منصرفات شركة')
                ->modalHeading('تسجيل منصرفات لشركة/مقاول')
                ->icon('heroicon-m-building-office')
                ->color('info')
                ->form(CurrencyTransactionResource::formSchema(CurrencyType::CompanyExpense))
                ->mutateFormDataUsing(function (array $data): array {
                    $data['type'] = CurrencyType::CompanyExpense;
                    return $data;
                }),

            // 3. زر إرسال عملة (Send)
            Actions\CreateAction::make('send')
                ->label('إرسال عملة (طرف ثالث)')
                ->modalHeading('إرسال عملة حرة لطرف ثانٍ/عميل')
                ->icon('heroicon-m-paper-airplane')
                ->color('warning')
                ->visible(false)
                ->form(CurrencyTransactionResource::formSchema(CurrencyType::SEND))
                ->mutateFormDataUsing(function (array $data): array {
                    $data['type'] = CurrencyType::SEND;
                    return $data;
                }),
        ];
    }
}
