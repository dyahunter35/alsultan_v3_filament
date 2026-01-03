<?php

namespace App\Filament\Resources\Companies\RelationManagers;

use App\Enums\CompanyType;
use App\Filament\Resources\Trucks\TruckResource;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class TrucksAsCompanyRelationManager extends RelationManager
{
    protected static string $relationship = 'trucksAsCompany';

    protected static ?string $relatedResource = TruckResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => $this->ownerRecord->trucksAsCompany())
            ->headerActions([
                Action::make('create')
                    ->icon(Heroicon::Plus)
                    ->label(__('truck.actions.create.label'))
                    ->action(
                        fn () => redirect(TruckResource::getUrl('create', ['company_id' => $this->ownerRecord]))
                    ),
            ]);
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord->type == CompanyType::Company;
    }
}
