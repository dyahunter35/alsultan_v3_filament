<?php

namespace App\Filament\Resources\Companies\Pages;

use App\Filament\Resources\Companies\CompanyResource;
use App\Filament\Resources\Companies\Widgets;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCompany extends ViewRecord
{

    protected static string $resource = CompanyResource::class;

    protected string $view = 'filament.resources.companies.pages.companies-view';

    protected function getHeaderWidgets(): array
    {
        return [
            //Widgets\Companystate::make(['record', $this->record]),
            Widgets\CompanyFinanceOverview::class,
        ];
    }



    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            Action::make('report')
                ->label(__('company.action.print_report'))
        ];
    }
}
