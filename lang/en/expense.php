<?php

$commonFields = include __DIR__ . '/partials/expense_fields.php';


return [
    'navigation' => [
        'group' => 'المالية',
        'label' => 'المنصرفات',
        'plural_label' => 'المنصرفات',
        'model_label' => 'منصرف',
        'icon' => 'heroicon-m-currency-dollar',
    ],
    'breadcrumbs' => [
        'index' => 'المنصرفات',
        'create' => 'إضافة منصرف',
        'edit' => 'تعديل المنصرف',
    ],

    'expenses_list' => [
        'navigation' => [
            'heading' => 'المنصرفات',

            'icon' => 'heroicon-m-currency-dollar',
        ],

        'fields' => array_merge($commonFields, [
            'amount' => [
                'label' => 'الكميات',
            ],
            'unit_price' => [
                'label' => 'السعر',
                'placeholder' => 'أدخل السعر',
            ],
        ]),
    ],

    'currency_expense' => [
        'navigation' => [
            'heading' => 'منصرفات العملات',
            'model_label' => 'منصرف عملة',
            'icon' => 'heroicon-m-currency-dollar',
        ],
        'fields' => array_merge($commonFields, [
            'amount' => [
                'label' => 'الكميات',
            ],
            'beneficiary' => [
                'label' => 'عميل العملة',
            ],
            'unit_price' => [
                'label' => 'السعر',
                'placeholder' => 'أدخل السعر',
            ],
        ]),
    ],

    'store_expense' =>  [
        'navigation' => [
            'heading' => 'منصرفات مخازن',
            'model_label' => 'منصرف مخازن',
            'icon' => 'heroicon-m-currency-dollar',
        ],
        'fields' => array_merge($commonFields, [
            'amount' => [
                'label' => 'الكميات',
            ],
            'unit_price' => [
                'label' => 'السعر',
                'placeholder' => 'أدخل السعر',
            ],
        ]),
    ],

    'financial_expense' =>  [
        'navigation' => [
            'heading' => 'معاملات مالية',
            'model_label' => 'معاملة مالية',
            'icon' => 'heroicon-m-currency-dollar',
        ],
        'fields' => array_merge($commonFields, [
            'amount' => [
                'label' => 'الكميات',
            ],
            'unit_price' => [
                'label' => 'السعر',
                'placeholder' => 'أدخل السعر',
            ],
        ]),
    ],

    'custom_expense' =>  [
        'navigation' => [
            'heading' => 'جمارك',
            'model_label' => 'معاملة جمارك',
            'icon' => 'heroicon-m-truck',
        ],
        'label' => [
            'plural' => 'الجمارك',
            'single' => 'جمارك',
        ],
        'fields' => array_merge($commonFields, [
            'amount' => [
                'label' => 'عدد العربات',
            ],
            'unit_price' => [
                'label' => 'سعر العربة',
                'placeholder' => 'أدخل السعر',
            ],
        ]),
    ],

    'tax_expense' =>  [
        'navigation' => [
            'heading' => 'ضرائب',
            'model_label' => 'معاملة ضرائب',
            'icon' => 'heroicon-m-currency-dollar',
        ],
        'label' => [
            'plural' => 'الضرائب',
            'single' => 'ضريبة',
        ],
        'fields' => array_merge($commonFields, [
            'amount' => [
                'label' => 'عدد العربات',
            ],
            'unit_price' => [
                'label' => 'سعر العربة',
                'placeholder' => 'أدخل السعر',
            ],
        ]),
    ],
];
