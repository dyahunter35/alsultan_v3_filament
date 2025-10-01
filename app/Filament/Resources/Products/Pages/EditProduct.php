<?php

namespace App\Filament\Resources\Products\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\Products\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
       // dd($data);
        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
