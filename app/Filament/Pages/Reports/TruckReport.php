<?php

namespace App\Filament\Pages\Reports;

use App\Filament\Pages\Concerns\HasReport;
use App\Models\Truck;
use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Livewire\Attributes\Url;

class TruckReport extends Page implements HasForms
{
    use HasReport;
    use InteractsWithForms;

    // protected static ?string $navigationIcon = 'heroicon-o-truck';
    // protected static ?string $navigationLabel = 'تقرير الشاحنة';
    protected string $view = 'filament.pages.reports.truck-report';

    #[Url]
    public ?int $truckId = null;

    public array $rows = [];
    public float $costPerGram = 0.0;
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

                        )
                    ]))
                ->searchable()
                ->reactive()
                ->afterStateUpdated(fn($state) => $this->loadForTruck($state)),
        ];
    }

    public function loadForTruck(int $truckId): void
    {
        $this->truck = Truck::with(['cargos.truck', 'expenses'])->findOrFail($truckId);

        // إجمالي المصروفات = كل المصروفات + النولون + العطلات
        $baseExpenses = $this->truck->expenses->sum('total_amount');
        $nolon = (float) $this->truck->truck_fare ?? 0;
        $extraDaysCost = (float) $this->truck->delay_value ?? 0;
        $totalExpenses = $baseExpenses + $nolon + $extraDaysCost;

        // إجمالي وزن البضائع
        $totalWeight = $this->truck->total_weight ?: 1;

        $this->costPerGram = $totalExpenses / $totalWeight;

        $rows = [];
        foreach ($this->truck->cargos as $cargo) {
            $weight = floatval($cargo->weight ?? 0);
            $totalCost = $weight * $this->costPerGram;
            $product = $cargo->product;
            $rows[] = [
                'cargo_id' => $cargo->id,
                'product_name' => $product->name,
                'truck_name' => $cargo->truck?->name ?? 'N/A',
                'weight_grams' => $weight,
                'quantity' => $cargo->quantity,
                'note' => $cargo->note,
                'cost_per_gram' => number_format($cargo->weight / $totalWeight * 100, 0) . '%',
                'total_cost' => round($totalCost, 2),
            ];
        }

        $this->rows = $rows;
    }
}
