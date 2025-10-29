<?php

namespace App\Filament\Clusters\Expanes\Pages;

use App\Filament\Clusters\Expanes\ExpanesCluster;
use App\Filament\Pages\Concerns\HasPage;
use App\Filament\Pages\Concerns\HasSinglePage;
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
    use HasTabs;
    use InteractsWithTable;
    use HasSinglePage;
    protected string $view = 'filament.clusters.expanes.pages.expenses-list';

    protected static ?string $cluster = ExpanesCluster::class;

    protected static ?int $navigationSort = 0;

    public static function getLocalePath(): string
    {
        return 'expense.' . static::className();
    }

    public function table(Table $table): Table
    {
        static::translateConfigureTable();
        return $table
            ->query(Expense::query())
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('type.label')
                    ->formatStateUsing(
                        fn($state, $record) =>
                        $record->expense_type_id
                            ? $record->type->label
                            : $record->custom_expense_type
                    )
                    ->badge(),


                Tables\Columns\TextColumn::make('beneficiary.name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('payer.name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->formatStateUsing(fn($state) => number_format($state))
                    ->sortable(),

                Tables\Columns\TextColumn::make('unit_price')
                    ->formatStateUsing(fn($state) => number_format($state, 2))
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_amount')
                    ->formatStateUsing(fn($state) => number_format($state))
                    ->color('success')
                    ->sortable(),

                Tables\Columns\TextColumn::make('remaining_amount')
                    ->formatStateUsing(fn($state) => number_format($state))
                    ->color('danger')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_paid')
                    ->boolean()
                    ->trueIcon('heroicon-s-check-circle')
                    ->falseIcon('heroicon-s-clock')
                    ->trueColor('success')
                    ->falseColor('warning'),

                Tables\Columns\TextColumn::make('payment_method')
                    ->badge(),
            ])
            ->filters([])
            ->recordActions([
                //ViewAction::make(),
                //EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
