<?php

namespace App\Filament\Clusters\Expanes\Pages;

use App\Filament\Clusters\Expanes\ExpanesCluster;
use App\Filament\Pages\Concerns\HasSinglePage;
use App\Models\Expense;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Page;
use Filament\Resources\Concerns\HasTabs;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class ExpensesList extends Page implements HasTable
{
    use HasSinglePage;
    use HasTabs;
    use InteractsWithTable;

    protected string $view = 'filament.clusters.expanes.pages.expenses-list';

    protected static ?string $cluster = ExpanesCluster::class;

    protected static ?int $navigationSort = 100;

    public static function getLocalePath(): string
    {
        return 'expense.'.static::className();
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
                        fn ($state, $record) => $record->expense_type_id
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
                    ->formatStateUsing(fn ($state) => number_format($state))
                    ->sortable(),

                Tables\Columns\TextColumn::make('unit_price')
                    ->formatStateUsing(fn ($state) => number_format($state, 2))
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_amount')
                    ->formatStateUsing(fn ($state) => number_format($state))
                    ->color('success')
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
            ->filters([
                Tables\Filters\SelectFilter::make('expense_type_id')
                    ->label(__('expense.filters.type.label'))
                    ->relationship('type', 'label'),
                /*Tables\Filters\SelectFilter::make('beneficiary_id')
                    ->label(__('expense.filters.beneficiary.label'))
                    ->relationship('beneficiary', 'name'),
                Tables\Filters\SelectFilter::make('payer_id')
                    ->label(__('expense.filters.payer.label'))
                    ->relationship('payer', 'name'), */
                Tables\Filters\Filter::make('created_at')
                    ->label(__('expense.filters.created_at.label'))
                    ->schema([
                        DatePicker::make('from')
                            ->label(__('expense.filters.created_at.fields.from.label')),
                        DatePicker::make('to')
                            ->label(__('expense.filters.created_at.fields.to.label')),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['from'],
                                fn ($query, $date) => $query->whereDate('created_at', '>=', $date)
                            )
                            ->when(
                                $data['to'],
                                fn ($query, $date) => $query->whereDate('created_at', '<=', $date)
                            );
                    }),
            ])
            ->recordActions([
                // ViewAction::make(),
                // EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
