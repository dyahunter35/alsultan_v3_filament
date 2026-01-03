<?php

$commonFields = include __DIR__.'/partials/expense_fields.php';

return [
    'navigation' => [
        'group' => 'Finance',
        'label' => 'Expenses',
        'plural_label' => 'Expenses',
        'model_label' => 'Expense',
        'icon' => 'heroicon-m-currency-dollar',
    ],
    'breadcrumbs' => [
        'index' => 'Expenses',
        'create' => 'Add Expense',
        'edit' => 'Edit Expense',
    ],

    'expenses_list' => [
        'navigation' => [
            'heading' => 'Expenses',
            'icon' => 'heroicon-m-currency-dollar',
        ],

        'fields' => array_merge($commonFields, [
            'amount' => [
                'label' => 'Quantity',
            ],
            'unit_price' => [
                'label' => 'Price',
                'placeholder' => 'Enter price',
            ],
        ]),
    ],

    'currency_expense' => [
        'navigation' => [
            'heading' => 'Currency Expenses',
            'model_label' => 'Currency Expense',
            'icon' => 'heroicon-m-currency-dollar',
        ],
        'fields' => array_merge($commonFields, [
            'amount' => [
                'label' => 'Quantity',
            ],
            'beneficiary' => [
                'label' => 'Currency Client',
            ],
            'unit_price' => [
                'label' => 'Price',
                'placeholder' => 'Enter price',
            ],
        ]),
    ],

    'store_expense' => [
        'navigation' => [
            'heading' => 'Store Expenses',
            'model_label' => 'Store Expense',
            'icon' => 'heroicon-m-currency-dollar',
        ],
        'fields' => array_merge($commonFields, [
            'amount' => [
                'label' => 'Quantity',
            ],
            'unit_price' => [
                'label' => 'Price',
                'placeholder' => 'Enter price',
            ],
        ]),
    ],

    'financial_expense' => [
        'navigation' => [
            'heading' => 'Financial Transactions',
            'model_label' => 'Financial Transaction',
            'icon' => 'heroicon-m-currency-dollar',
        ],
        'fields' => array_merge($commonFields, [
            'amount' => [
                'label' => 'Quantity',
            ],
            'unit_price' => [
                'label' => 'Price',
                'placeholder' => 'Enter price',
            ],
        ]),
    ],

    'custom_expense' => [
        'navigation' => [
            'heading' => 'Customs',
            'model_label' => 'Customs Transaction',
            'icon' => 'heroicon-m-truck',
        ],
        'label' => [
            'plural' => 'Customs',
            'single' => 'Customs',
        ],
        'fields' => array_merge($commonFields, [
            'amount' => [
                'label' => 'Number of Vehicles',
            ],
            'unit_price' => [
                'label' => 'Vehicle Price',
                'placeholder' => 'Enter price',
            ],
        ]),
    ],

    'tax_expense' => [
        'navigation' => [
            'heading' => 'Taxes',
            'model_label' => 'Tax Transaction',
            'icon' => 'heroicon-m-currency-dollar',
        ],
        'label' => [
            'plural' => 'Taxes',
            'single' => 'Tax',
        ],
        'fields' => array_merge($commonFields, [
            'amount' => [
                'label' => 'Number of Vehicles',
            ],
            'unit_price' => [
                'label' => 'Vehicle Price',
                'placeholder' => 'Enter price',
            ],
        ]),
    ],
];
