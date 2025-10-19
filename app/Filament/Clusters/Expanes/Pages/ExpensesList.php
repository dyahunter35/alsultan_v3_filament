<?php

namespace App\Filament\Clusters\Expanes\Pages;

use App\Filament\Clusters\Expanes\ExpanesCluster;
use App\Filament\Pages\Concerns\HasPage;
use App\Models\Expense;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Pages\Page;
use Filament\Resources\Concerns\HasTabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class ExpensesList extends Page implements HasTable
{
    use HasPage;
    use HasTabs;
    use InteractsWithTable;

    protected string $view = 'filament.clusters.expanes.pages.expenses-list';

    protected static ?string $cluster = ExpanesCluster::class;

    public function table(Table $table): Table
    {
        // static::translateConfigureTable();
        return $table
            ->query(Expense::query())
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('expense_type')
                    ->label('النوع')
                    ->badge(),

                Tables\Columns\TextColumn::make('beneficiary.rep_name')
                    ->label('المستفيد')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('payer.rep_name')
                    ->label('الدافع')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('الكمية')
                    ->formatStateUsing(fn($state) => number_format($state))
                    ->sortable(),

                Tables\Columns\TextColumn::make('unit_price')
                    ->label('سعر الوحدة')
                    ->formatStateUsing(fn($state) => number_format($state, 2))
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('الإجمالي')
                    ->formatStateUsing(fn($state) => number_format($state))
                    ->color('success')
                    ->sortable(),

                Tables\Columns\TextColumn::make('remaining_amount')
                    ->label('المتبقي')
                    ->formatStateUsing(fn($state) => number_format($state))
                    ->color('danger')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_paid')
                    ->label('الحالة')
                    ->boolean()
                    ->trueIcon('heroicon-s-check-circle')
                    ->falseIcon('heroicon-s-clock')
                    ->trueColor('success')
                    ->falseColor('warning'),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('وسيلة الدفع')
                    ->badge(),
            ])
            ->filters([])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public function getTabs(): array
    {
        return [
            // 1. زر القائمة الافتراضية (عرض جميع السجلات)
            'all' => Tab::make('جميع المصروفات'),

            // 2. Tab للعملة (Currency)
            'create-currency' => Tab::make('صرف عملة')
                ->badgeColor('success')
                ->modifyQueryUsing(fn($query) => $query->where('expense_type', \App\Enums\ExpansesType::CURRENCY))

            /* // 3. Tab للجمارك (Customs)
            'create-customs' => Tab::make('مصروف جمارك')
                ->badgeColor('warning')
                ->modifyQueryUsing(fn ($query) => $query->where('expense_type', \App\Enums\ExpansesType::CUSTOMS))
                ->url(ExpenseResource::getUrl('create-customs')),

            // 4. Tab للحكومة (Gov)
            'create-gov' => Tab::make('مصروف حكومي')
                ->badgeColor('info')
                ->modifyQueryUsing(fn ($query) => $query->where('expense_type', \App\Enums\ExpansesType::GOVERMENT))
                ->url(ExpenseResource::getUrl('create-gov')),

            // 5. Tab للضرائب (Tax)
            'create-tax' => Tab::make('ضرائب')
                ->badgeColor('danger')
                ->modifyQueryUsing(fn ($query) => $query->where('expense_type', \App\Enums\ExpansesType::TAX))
                ->url(ExpenseResource::getUrl('create-tax')),

            // 6. Tab للمخازن (Stores)
            'create-stores' => Tab::make('مصروف مخازن')
                ->badgeColor('primary')
                ->modifyQueryUsing(fn ($query) => $query->where('expense_type', \App\Enums\ExpansesType::STORES))
                ->url(ExpenseResource::getUrl('create-stores')),
 */
            // 7. Tab للتحويلات (Transaction)
            /* 'create-transaction' => Tab::make('تحويلات مالية')
                ->badgeColor('secondary')
                ->modifyQueryUsing(fn ($query) => $query->where('expense_type', \App\Enums\ExpansesType::REP_TRA)) // افترضنا نوع التحويل من Enumerator
                ->url(ExpenseResource::getUrl('create-transaction')), */
        ];
    }
}
