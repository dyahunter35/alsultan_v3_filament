<?php

return [
    'navigation' => [
        'group' => 'إدارة الشاحنات',
        'label' => 'الموانئ',
        'plural_label' => 'الموانئ',
        'model_label' => 'ميناء',
    ],
    'breadcrumbs' => [
        'index' => 'الموانئ',
        'create' => 'إضافة ميناء',
        'edit' => 'تعديل ميناء',
    ],
    'fields' => [
        'name' => [
            'label' => 'الاسم',
            'placeholder' => 'أدخل الاسم',
        ],
        'description' => [
            'label' => 'الوصف',
            'placeholder' => '',
        ],

        'created_at' => [
            'label' => 'تاريخ الإنشاء',
            'placeholder' => '',
        ],
        'updated_at' => [
            'label' => 'آخر تعديل',
            'placeholder' => '',
        ],
    ],

];
