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
];
