<?php

namespace App\Filament\Resources\Expenses\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Table;
use Filament\Tables;

class ExpensesTable
{
    public static function configure(Table $table): Table
    {
        return $table
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
            ->filters([
                //
            ])
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
