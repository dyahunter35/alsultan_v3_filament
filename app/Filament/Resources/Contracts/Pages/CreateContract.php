<?php

namespace App\Filament\Resources\Contracts\Pages;

use App\Filament\Resources\Contracts\ContractResource;
use App\Models\Company;
use App\Models\Contract;
use App\Models\ContractItem;
use App\Models\Document;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;

class CreateContract extends CreateRecord
{
    protected static string $resource = ContractResource::class;

    public function mount(): void
    {
        $company = Company::inRandomOrder()->first() ?? Company::factory()->create();
        $user = User::inRandomOrder()->first();

        $this->form->fill([
            'reference_no' => Contract::generateContractNumber(),
            'title' => 'Service Agreement',
            'company_id' => $company->id,
            'effective_date' => now(),
            'duration_months' => rand(6, 24),
            'total_amount' => rand(1000, 50000),
            'scope_of_services' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'confidentiality_clause' => 'All information is confidential.',
            'termination_clause' => 'Termination with 30 days notice.',
            'governing_law' => 'Delaware, USA',
            'created_by' => $user?->id,
            // توليد عناصر عشوائية
            'items' => ContractItem::factory()->count(3)->make()->toArray(),
            // توليد ملفات عشوائية
            'documents' => Document::factory()->count(2)

                ->state(fn() => [
                    'documentable_id' => null,
                    'documentable_type' => Contract::class,
                ])->make()->toArray(),
        ]);
    }
}
