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

                Tables\Columns\TextColumn::make('type.label')
                    ->label('نوع المنصرف')
                    ->formatStateUsing(
                        fn($state, $record) =>
                        $record->expense_type_id
                            ? $record->type->label
                            : $record->custom_expense_type
                    )
                    ->badge(),


                Tables\Columns\TextColumn::make('beneficiary.name')
                    ->label('المستفيد')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('payer.name')
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
}
