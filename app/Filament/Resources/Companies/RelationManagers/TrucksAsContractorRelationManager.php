<?php

namespace App\Filament\Resources\Companies\RelationManagers;

use App\Enums\CompanyType;
use App\Filament\Resources\Trucks\TruckResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class TrucksAsContractorRelationManager extends RelationManager
{
    protected static string $relationship = 'trucksAsContractor';

    protected static ?string $relatedResource = TruckResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn() => $this->ownerRecord->trucksAsContractor())
            ->headerActions([
                Action::make('create')
                    //->icon('heroicons-m-plus')
                    ->label(__('truck.actions.create.label'))
                    ->action(
                        fn() =>
                        redirect(TruckResource::getUrl('create', ['contractor_id' =>  $this->ownerRecord]))
                    )

            ]);
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord->type == CompanyType::Contractor;
    }
}
