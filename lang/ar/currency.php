<?php
return [
    'label' => [
        'plural' => 'سجلات العملات',
        'single' => 'معاملة مالية',
    ],

    'fields' => [

        'code' => [
            'label' => 'العملة',
            'placeholder' => 'ادخل الرمز',
        ],
        'user' => [
            'label' => 'المستخدم',
            'placeholder' => 'ادخل اسم المستخدم',
            'default' => 'دفع مباشر',
        ],
        'causer' => [
            'label' => 'المنفذ',
            'placeholder' => 'ادخل اسم المنفذ',
        ],
        'rate' => [
            'label' => 'سعر الصرف',
            'placeholder' => 'ادخل سعر الصرف',
        ],
        'balance' => [
            'label' => 'الرصيد',
            'placeholder' => 'ادخل الرصيد',
        ],
        'note' => [
            'label' => 'ملاحظة',
            'placeholder' => 'ادخل ملاحظة',
        ],
        'created_at' => [
            'label' => 'تاريخ الإنشاء',
        ],
        'updated_at' => [
            'label' => 'تاريخ التحديث',
        ],
    ],
    'filters' => [
        'issuance_date' => [
            'label' => 'فلترة حسب تاريخ الإصدار',
        ],
    ],
    'actions' => [
        'edit' => 'تعديل',
        'delete' => 'حذف',
    ],
    'widgets' => [
        'state' => [
            'label' => 'حسابات العملة'
        ]
    ]
];
