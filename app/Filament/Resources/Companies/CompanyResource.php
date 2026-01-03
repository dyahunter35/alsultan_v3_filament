<?php

namespace App\Filament\Resources\Companies;

use App\Filament\Pages\Concerns\HasResource;
use App\Filament\Resources\Companies\Pages\CreateCompany;
use App\Filament\Resources\Companies\Pages\EditCompany;
use App\Filament\Resources\Companies\Pages\ListCompanies;
use App\Filament\Resources\Companies\Pages\ViewCompany;
use App\Filament\Resources\Companies\Schemas\CompanyForm;
use App\Filament\Resources\Companies\Schemas\CompanyInfolist;
use App\Filament\Resources\Companies\Tables\CompaniesTable;
use App\Models\Company;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CompanyResource extends Resource
{
    use HasResource;

    protected static ?string $model = Company::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?int $navigationSort = 9;

    public static function form(Schema $schema): Schema
    {
        static::translateConfigureForm();

        return CompanyForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        static::translateConfigureInfolist();

        return CompanyInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        static::translateConfigureTable();

        return CompaniesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TrucksAsCompanyRelationManager::class,
            RelationManagers\TrucksAsContractorRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCompanies::route('/'),
            'create' => CreateCompany::route('/create'),
            'view' => ViewCompany::route('/{record}'),
            'edit' => EditCompany::route('/{record}/edit'),
            // 'repo' => CompaniesReport::route('/report'),
        ];
    }

    /**
     * @return array<class-string<Widget>>
     */
    public static function getWidgets(): array
    {
        return [
            // Companystate::class
        ];
    }
}
