<?php

namespace App\Filament\Resources\Contracts\Schemas;

use App\Filament\Forms\Components\DecimalInput;
use Filament\Schemas;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Schemas\Schema;

class ContractForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Schemas\Components\Section::make('contract_info')
                    ->schema([
                        Forms\Components\TextInput::make('reference_no')
                            ->readOnly()
                            ->required(),

                        Forms\Components\TextInput::make('title')->required(),
                        Forms\Components\Select::make('company_id')
                            ->relationship('company', 'name')
                            ->required(),
                        Forms\Components\DatePicker::make('effective_date'),
                        Forms\Components\TextInput::make('duration_months'),
                        DecimalInput::make('total_amount')
                            ->live(onBlur: true)
                            ->numeric()
                            ->prefix('$'),
                        Forms\Components\Textarea::make('scope_of_services')
                            ->rows(3)
                            ->columnSpan('full'),

                    ])->columns(2)
                    ->columnSpan(2),

                Schemas\Components\Section::make()
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship('items') // ✅ ربط الـ Repeater بالعلاقة
                            ->schema([
                                Forms\Components\TextInput::make('description')
                                    ->label(__('contract.fields.items.fields.description.label'))
                                    ->required(),

                                Forms\Components\TextInput::make('size')
                                    ->label(__('contract.fields.items.fields.size.label'))
                                    ->required(),

                                DecimalInput::make('machine_count')
                                    ->label(__('contract.fields.items.fields.machine_count.label')),
                                DecimalInput::make('quantity')
                                    ->label(__('contract.fields.items.fields.quantity.label'))

                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $set('total_price', $get('unit_price') ?? 1 * $state ?? 0);
                                        $set('total_weight', $get('weight') ?? 1 * $state ?? 0);
                                    })
                                    ->live(onBlur: true),

                                Forms\Components\TextInput::make('weight')
                                    ->label(__('contract.fields.items.fields.weight.label'))
                                    ->numeric()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $set('total_weight', $get('quantity') ?? 1 * $state ?? 0);
                                    })
                                    ->live(onBlur: true)
                                    ->default(1),

                                DecimalInput::make('unit_price')
                                    ->label(__('contract.fields.items.fields.unit_price.label'))
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $set('total_price', $get('quantity') ?? 1 * $state ?? 0);
                                    })
                                    ->live(onBlur: true),

                                DecimalInput::make('total_weight')
                                    ->label(__('contract.fields.items.fields.total_weight.label'))
                                    ->readOnly()
                                    ->dehydrated(false)
                                    ->reactive()
                                    ->suffix('kg'),

                                // ✅ عرض الإجماليات المحسوبة تلقائياً
                                DecimalInput::make('total_price')
                                    ->label(__('contract.fields.items.fields.total_price.label'))
                                    ->readOnly()
                                    ->dehydrated(false)
                                    ->reactive()
                                    ->suffix('$'),


                            ])
                            ->columns(2)
                    ])
                    ->columnSpan(2),


                Schemas\Components\Section::make('documents')
                    ->schema([

                        Forms\Components\Repeater::make('document')
                            ->label(null)

                            ->relationship('documents')
                            ->schema([
                                Forms\Components\DatePicker::make('issuance_date'),
                                Forms\Components\TextInput::make('file_type')->label('Type'),

                                // Forms\Components\TextInput::make('name')->label('Document Name'),
                                SpatieMediaLibraryFileUpload::make('file')
                                    ->collection('contract_docs'),
                                Forms\Components\Textarea::make('description')->rows(2),
                            ])
                            ->columns(2)
                            ->collapsible()
                            //->orderable('issuance_date')
                            ->createItemButtonLabel('Add Document'),
                    ])
                    ->columnSpan(2),

                Schemas\Components\Section::make('clauses')
                    ->schema([
                        Forms\Components\Textarea::make('confidentiality_clause'),
                        Forms\Components\Textarea::make('termination_clause'),
                        Forms\Components\TextInput::make('governing_law'),

                    ])
                    ->visible(true)
                    ->columnSpan(2),
            ])->columns([
                'lg' => 3,
                'sm' => 2
            ]);
    }
}
