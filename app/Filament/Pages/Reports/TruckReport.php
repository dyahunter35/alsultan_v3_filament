<?php

namespace App\Filament\Pages\Reports;

use App\Filament\Pages\Concerns\HasReport;
use App\Models\Truck;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Livewire\Attributes\Url;

class TruckReport extends Page implements HasForms
{
    use HasReport;
    use InteractsWithForms;

    protected static ?int $navigationSort = 36;

    // protected static ?string $navigationIcon = 'heroicon-o-truck';
    // protected static ?string $navigationLabel = 'تقرير الشاحنة';
    protected string $view = 'filament.pages.reports.truck-report';

    #[Url]
    public ?int $truckId = null;

    public array $rows = [];

    public ?Truck $truck = null;

    public function mount(): void
    {
        if ($this->truckId) {
            $this->loadForTruck($this->truckId);
        }
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Select::make('truckId')
                ->label('اختر الشاحنة')
                ->options(Truck::query()->get()
                    ->mapWithKeys(fn(Truck $truck) => [
                        $truck->id => sprintf(
                            '(%s) %s - %s',
                            $truck->id,
                            $truck->driver_name,
                            $truck->from?->name,

                        ),
                    ]))
                ->searchable()
                ->reactive()
                ->afterStateUpdated(fn($state) => $this->loadForTruck($state)),
        ];
    }

    public function loadForTruck(int $truckId): void
    {
        $this->truck = Truck::with(['cargos.truck', 'expenses'])->findOrFail($truckId);

        $rows = [];

        foreach ($this->truck->cargos as $cargo) {
            $qty = ($cargo->quantity ?? 0);
            $r_qty = ($cargo->real_quantity ?? 0);
            $dif = $r_qty - $qty;
            $product = $cargo->product;

            $rows[] = [
                'cargo_id' => $cargo->id,
                'product_name' => $product->name,
                'truck_name' => $cargo->truck?->name ?? 'N/A',
                'weight_grams' => $cargo->weight ?? 0,
                'weight_ton' => floatval($cargo->ton_weight ?? 0),
                'quantity' => $qty,
                'unit_quantity' => $cargo->unit_quantity,
                'real_quantity' => $r_qty,
                'dif' => $dif,
                'note' => $cargo->note,
            ];
        }

        $this->rows = $rows;
    }
}
