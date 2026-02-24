<?php

namespace App\Filament\Pages\Reports;

use App\Filament\Pages\Concerns\HasReport;
use App\Models\Truck;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Livewire\Attributes\Url;

class TruckReport extends Page implements HasForms
{
    use HasReport;
    use InteractsWithForms;

    protected static ?int $navigationSort = 36;

    // protected static ?string $navigationIcon = 'heroicon-o-truck';
    // protected static ?string $navigationLabel = 'تقرير الشاحنة';
    protected string $view = 'filament.pages.reports.truck-report';

    #[Url] public ?int $truckId = null;
    #[Url] public ?string $type = 'outer';

    public array $rows = [];

    public ?Truck $truck = null;

    public function mount(): void
    {
        if ($this->truckId) {
            $this->loadForTruck();
        }
    }

    protected function getFormSchema(): array
    {
        return [
            Grid::make(3)
                ->schema([

                        Forms\Components\Select::make('truckId')
                            ->label('اختر الشاحنة')
                            ->options(
                                function (Get $get) {
                                    $type = $get('type');

                                    if (!$type)
                                        return [];

                                    return Truck::query()
                                        ->where('type', $type)
                                        ->get()
                                        ->mapWithKeys(fn(Truck $truck) => [
                                            $truck->id => sprintf(
                                                "\u{200E}(%s) %s - %s", // أضفت الـ LRM هنا أيضاً لضمان التنسيق في القائمة
                                                $truck->code,
                                                $truck->driver_name,
                                                $truck->from?->name ?? 'بدون منطقة'
                                            ),
                                        ]);
                                }
                            )
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(fn($state) => [$this->truckId = $state, $this->loadForTruck()]),

                        Forms\Components\Select::make('type')
                            ->label('نوع الشاحنة')
                            ->options([
                                    'outer' => 'خارجية',
                                    'local' => 'داخلية',
                                ])
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(fn($state) => [$this->truckId = null, $this->loadForTruck()]),
                    ])

        ];
    }

    public function loadForTruck(): void
    {
        if (!$this->truckId)
            return;

        $this->truck = Truck::with(['cargos.truck', 'expenses'])->findOrFail($this->truckId);

        $rows = [];

        foreach ($this->truck->cargos as $cargo) {
            $qty = ($cargo->quantity ?? 0);
            $r_qty = ($cargo->real_quantity ?? 0);
            $dif = $r_qty - $qty;
            $product = $cargo->product;

            $rows[] = [
                'cargo_id' => $cargo->code,
                'product_name' => $product->name,
                'size' => $cargo->size,
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
