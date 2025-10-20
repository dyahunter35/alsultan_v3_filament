<?php

namespace App\Filament\Resources\Customers;

use App\Enums\ExpenseGroup;
use App\Enums\ExpenseType;
use App\Filament\Pages\Concerns\HasResource;
use App\Filament\Pages\Reports\CustomersReport;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Filament\Resources\Customers\Pages\CreateCustomer;
use App\Filament\Resources\Customers\Pages\EditCustomer;
use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Models\Customer;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomerResource extends Resource
{
    use HasResource;
    protected static ?string $model = Customer::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-m-user';
    protected static ?int $navigationSort = 2;



    public static function form(Schema $schema): Schema
    {
        static::translateConfigureForm();
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->maxLength(255),

                        TextInput::make('phone')
                            ->tel()
                            ->maxLength(255)
                            ->default(null),

                        Select::make('permanent')
                            ->options(ExpenseGroup::class)
                            ->default(ExpenseGroup::SALE->value)
                    ])->columnSpan(2)
                    ->columns(2),
                Section::make()
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('photo')
                            ->collection('customer_photos'),
                    ])->columnSpan(1),

            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        static::translateConfigureTable();
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('photo')
                    ->collection('customer_photos')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('permanent')
                    ->badge()
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make()
                    ->visible(auth()->user()->can('restore_customer')),
            ])
            ->recordActions([

                EditAction::make(),
                DeleteAction::make()
                    ->visible(fn($record) => !$record->deleted_at),
                RestoreAction::make()
                    ->visible(fn($record) => $record->deleted_at),
                ForceDeleteAction::make()
                    ->visible(fn($record) => $record->deleted_at),

                ActionGroup::make([
                    Action::make('report')
                        ->label(__('customer.reports.ledger.title'))
                        ->url(fn(Customer $record): string => CustomersReport::getUrl(['customerId' => $record->id]))
                        ->openUrlInNewTab(),
                ]),

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    public static function getNavigationBadge(): ?string
    {
        /** @var class-string<Model> $modelClass */
        return $modelClass = static::getEloquentQuery()->count();

        return (string) Customer::count();
    }
    public static function getPages(): array
    {
        return [
            'index' => ListCustomers::route('/'),
            'create' => CreateCustomer::route('/create'),
            'edit' => EditCustomer::route('/{record}/edit'),
        ];
    }
}
