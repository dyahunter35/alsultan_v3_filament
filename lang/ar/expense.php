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
        'edit' => 'تعديل منصرف',
    ],

    'expenses_list' => [
        'navigation' => [
            'heading' => 'المنصرفات',
            'icon' => 'heroicon-m-currency-dollar',
        ],

        'fields' => array_merge($commonFields, [
            'amount' => [
                'label' => 'الكمية',
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
                'label' => 'الكمية',
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

    'store_expense' => [
        'navigation' => [
            'heading' => 'منصرفات المخازن',
            'model_label' => 'منصرف مخزن',
            'icon' => 'heroicon-m-currency-dollar',
        ],
        'fields' => array_merge($commonFields, [
            'amount' => [
                'label' => 'الكمية',
            ],
            'unit_price' => [
                'label' => 'السعر',
                'placeholder' => 'أدخل السعر',
            ],
        ]),
    ],

    'financial_expense' => [
        'navigation' => [
            'heading' => 'المعاملات المالية',
            'model_label' => 'معاملة مالية',
            'icon' => 'heroicon-m-currency-dollar',
        ],
        'fields' => array_merge($commonFields, [
            'amount' => [
                'label' => 'الكمية',
            ],
            'unit_price' => [
                'label' => 'السعر',
                'placeholder' => 'أدخل السعر',
            ],
        ]),
    ],

    'custom_expense' => [
        'navigation' => [
            'heading' => 'الجمارك',
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

    'tax_expense' => [
        'navigation' => [
            'heading' => 'الضرائب',
            'model_label' => 'معاملة ضريبية',
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
    'shipment_expense' => [
        'navigation' => [
            'heading' => 'التخليص والشحن',
            'model_label' => 'معاملة تخليص وشحن',
            'icon' => 'heroicon-m-truck',
        ],
        'label' => [
            'plural' => 'التخليص والشحن',
            'single' => 'تخليص وشحن',
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
