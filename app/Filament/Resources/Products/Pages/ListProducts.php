<?php

namespace App\Filament\Resources\Products\Pages;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use App\Filament\Resources\Products\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [

            Action::make('report')
                ->label(__('product.actions.report.label'))
                ->icon(__('heroicon-o-document'))
                ->color('info')
                ->visible(fn()=>!auth()->user()->hasRole('بائع'))
                ->url(ProductResource::getUrl('report')),

            Action::make('report')
                ->label(__('product.actions.branch_report.label'))
                ->icon(__('heroicon-m-printer'))
                ->color('success')
                ->url(ProductResource::getUrl('branch')),

            CreateAction::make(),
        ];
    }
}
