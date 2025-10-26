<?php
return [
    'navigation' => [
        'group' => 'Finance',
        'label' => 'CurrencyTransactions',
        'plural_label' => 'CurrencyTransactions',
        'model_label' => 'CurrencyTransaction',
        'icon' => 'heroicon-m-currency-dollar',
    ],
    'breadcrumbs' => [
        'index' => 'CurrencyTransactions',
        'create' => 'Add CurrencyTransaction',
        'edit' => 'Edit CurrencyTransaction',
    ],
    'fields' => [
        'currency_id' => [
            'label' => 'Currency Id',
            'placeholder' => '',
        ],
        'party' => [
            'label' => 'Party',
        ],
        'party_type' => [
            'label' => 'Party Type',
            'placeholder' => '',
        ],
        'party_id' => [
            'label' => 'Party Id',
            'placeholder' => '',
        ],
        'amount' => [
            'label' => 'Amount',
            'placeholder' => '',
        ],
        'rate' => [
            'label' => 'Rate',
            'placeholder' => '',
        ],
        'total' => [
            'label' => 'Total',
            'placeholder' => '',
        ],
        'type' => [
            'label' => 'Type',
            'placeholder' => '',
        ],
        'note' => [
            'label' => 'Note',
            'placeholder' => '',
        ],
        'created_at' => [
            'label' => 'Created At',
            'placeholder' => '',
        ],
        'updated_at' => [
            'label' => 'Updated At',
            'placeholder' => '',
        ],
        'deleted_at' => [
            'label' => 'Deleted At',
            'placeholder' => '',
        ],
    ],
];
