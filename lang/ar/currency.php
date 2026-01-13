<?php

return [
    'navigation' => [
        'group' => 'المالية',
        'label' => 'العملات',
        'plural_label' => 'العملات',
        'model_label' => 'عملة',
    ],
    'breadcrumbs' => [
        'index' => 'العملات',
        'create' => 'إضافة عملة',
        'edit' => 'تعديل العملة',
    ],
    'fields' => [
        'name' => [
            'label' => 'الاسم',
            'placeholder' => '',
        ],
        'code' => [
            'label' => 'الرمز',
            'placeholder' => 'SDG',
        ],
        'symbol' => [
            'label' => 'العلامة',
            'placeholder' => '$',
        ],
        'exchange_rate' => [
            'label' => 'سعر الصرف',
            'placeholder' => '',
        ],
        'created_at' => [
            'label' => 'تاريخ الإنشاء',
            'placeholder' => '',
        ],
        'updated_at' => [
            'label' => 'آخر تحديث',
            'placeholder' => '',
        ],
    ],
    'widgets' => [
        'state' => [
            'label' => 'حسابات العملات'
        ]
    ]
];
