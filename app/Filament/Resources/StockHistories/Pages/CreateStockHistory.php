<?php

namespace App\Filament\Resources\StockHistories\Pages;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use App\Filament\Resources\StockHistories\StockHistoryResource;
use App\Models\Product;
use App\Services\InventoryService;
use Filament\Forms;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Enums\StockCase;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model; // تأكد من استيراد هذا الكلاس

class CreateStockHistory extends CreateRecord
{
    protected static string $resource = StockHistoryResource::class;


    /* protected function getFormSchema(): array
    {
        return [
            Section::make('تحديث المخزون')
                ->schema([
                    Select::make('product_id')
                        ->label(__('stock_history.fields.product_id.label'))
                        ->options(
                            Product::whereHas('branches', fn($query) => $query->where('branches.id', Filament::getTenant()->id))
                                ->get()
                                ->mapWithKeys(fn(Product $product) => [
                                    $product->id => sprintf(
                                        '%s - %s [%s]',
                                        $product->name,
                                        $product->category?->name,
                                        $product->stock_for_current_branch
                                    )
                                ])
                        )
                        ->preload()
                        ->searchable()
                        ->required(),

                    Select::make('type')
                        ->label(__('stock_history.fields.type.label'))
                        ->required()
                        ->options(StockCase::class)
                        ->default(StockCase::Increase),

                    TextInput::make('quantity_change')
                        ->label(__('stock_history.fields.quantity_change.label'))
                        ->placeholder(__('stock_history.fields.quantity_change.placeholder'))
                        ->numeric()
                        ->required(),

                    Textarea::make('notes')
                        ->label(__('stock_history.fields.notes.label'))
                        ->placeholder(__('stock_history.fields.notes.placeholder'))
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->columnSpan(2),
        ];
    } */

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // أتركها فارغة إذا تريد استخدام Service فقط
        return $data;
    }

    /* protected function handleRecordCreation(array $data): Model
    {
        $inventoryService = new InventoryService();
        $branch = Filament::getTenant();
        $user = Auth::user();
        $product = Product::find($data['product_id']);

        if (!$product) {
            Notification::make()
                ->title('المنتج غير موجود')
                ->danger()
                ->send();
            throw new Exception('المنتج غير موجود');
        }

        try {
            if (in_array($data['type'], [StockCase::Increase, StockCase::Initial])) {

                return $inventoryService->addStockForBranch(
                    $product,
                    $branch,
                    $data['quantity_change'],
                    $data['notes'] ?? null,
                    $user
                );
            }
            return $inventoryService->deductStockForBranch(
                $product,
                $branch,
                $data['quantity_change'],
                $data['notes'] ?? null,
                $user
            );
        } catch (Exception $e) {
            Notification::make()
                ->title('خطأ في المخزون')
                ->body($e->getMessage())
                ->danger()
                ->send();

            throw $e; // أي خطأ سيوقف الـ transaction
        }
    } */
    protected function handleRecordCreation(array $data): Model
    {
        \Log::info('handleRecordCreation start', $data);

        $inventoryService = new InventoryService();
        $branch = Filament::getTenant();
        $user = Auth::user();
        $product = Product::find($data['product_id']);

        \Log::info('Branch', [$branch]);
        \Log::info('User', [$user]);
        \Log::info('Product', [$product]);

        if (!$product) {
            \Log::error('المنتج غير موجود');
            throw new \Exception('المنتج غير موجود');
        }

        try {
            if ($data['type'] === StockCase::Increase || $data['type'] === StockCase::Initial) {
                $result = $inventoryService->addStockForBranch($product, $branch, $data['quantity_change'], $data['type'], $data['notes'] ?? null, $user);
                \Log::info('Result from addStockForBranch', [$result]);
                return $result;
            } else {
                $result = $inventoryService->deductStockForBranch($product, $branch, $data['quantity_change'], $data['notes'] ?? null, $user);
                \Log::info('Result from deductStockForBranch', [$result]);
                return $result;
            }
        } catch (\Exception $e) {
            \Log::error('Exception in handleRecordCreation: ' . $e->getMessage());
            throw $e;
        }
        return $result->fresh();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
