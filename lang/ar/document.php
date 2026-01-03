<?php

return [
    'label' => [
        'plural' => 'المستندات',
        'single' => 'مستند',
    ],

    'fields' => [
        'name' => [
            'label' => 'اسم المستند',
            'placeholder' => 'أدخل اسم المستند',
        ],
        'issuance_date' => [
            'label' => 'تاريخ الإصدار',
            'placeholder' => 'أدخل تاريخ الإصدار',
        ],
        'type' => [
            'label' => 'نوع المستند',
            'placeholder' => 'أدخل نوع المستند',
        ],
        'file' => [
            'label' => 'الملف',
            'placeholder' => 'قم برفع الملف',
        ],
        'note' => [
            'label' => 'ملاحظات',
            'placeholder' => 'أدخل الملاحظات',
        ],
        'created_at' => [
            'label' => 'تاريخ الإنشاء',
        ],
        'updated_at' => [
            'label' => 'آخر تعديل',
        ],
    ],
    'filters' => [
        'issuance_date' => [
            'label' => 'تصفية حسب تاريخ الإصدار',
        ],
    ],
    'actions' => [
        'edit' => 'تعديل',
        'delete' => 'حذف',
    ],
];
