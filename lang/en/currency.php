<?php

return [
    'navigation' => [
        'group' => 'Finance',
        'label' => 'Currencies',
        'plural_label' => 'Currencies',
        'model_label' => 'Currency',
        'icon' => 'heroicon-m-wallet',
    ],
    'breadcrumbs' => [
        'index' => 'Currencies',
        'create' => 'Add Currency',
        'edit' => 'Edit Currency',
    ],
    'fields' => [

        'name' => [
            'label' => 'Name',
            'placeholder' => '',
        ],
        'code' => [
            'label' => 'Code',
            'placeholder' => 'SDG',
        ],
        'symbol' => [
            'label' => 'Symbol',
            'placeholder' => '$',
        ],
        'exchange_rate' => [
            'label' => 'Exchange Rate',
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
    ],
];
