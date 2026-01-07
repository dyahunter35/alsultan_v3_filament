<?php

namespace App\Filament\Resources\Trucks\RelationManagers;

use App\Filament\Clusters\Expanes\Pages\CustomExpense;
use App\Filament\Pages\Concerns\HasRelationManager;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomExpensesRelationManager extends RelationManager
{
    use HasRelationManager;

    protected static string $relationship = 'customExpenses';

    public static function getLocalePath(): string
    {
        return 'expense.custom_expense';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components(
                CustomExpense::expenseForm($this->ownerRecord->id)
            )->columns(1);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns(CustomExpense::expenseTableColumns())
            ->filters([
                TrashedFilter::make(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->preserveFormDataWhenCreatingAnother(fn (array $data): array => $data),
                // AssociateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                // DissociateAction::make(),
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->withoutGlobalScopes([
                    SoftDeletingScope::class,
                ]));
    }
}
