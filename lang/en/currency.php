<?php
return [
    'label' => [
        'plural' => 'Courrency Histories',
        'single' => 'financial transaction',
    ],

    'fields' => [

        'code' => [
            'label' => 'Currency Code',
            'placeholder' => 'Enter code',
        ],
        'user' => [
            'label' => 'Currency User',
            'placeholder' => 'Enter currency user',
            'default' => 'Direct Payment',
        ],
        'causer' => [
            'label' => 'Causer',
            'placeholder' => 'Enter causer',
        ],
        'rate' => [
            'label' => 'Rate',
            'placeholder' => 'Enter rate',
        ],
        'balance' => [
            'label' => 'Balance',
            'placeholder' => 'Enter balance',
        ],
        'note' => [
            'label' => 'Note',
            'placeholder' => 'Enter note',
        ],
        'created_at' => [
            'label' => 'Created At',
        ],
        'updated_at' => [
            'label' => 'Updated At',
        ],
    ],
    'filters' => [
        'issuance_date' => [
            'label' => 'Filter by Issuance Date',
        ],
    ],
    'actions' => [
        'edit' => 'تعديل',
        'delete' => 'حذف',
    ],
    'widgets' => [
        'state' => [
            'label' => 'Currency Financial'
        ]
    ]
];
