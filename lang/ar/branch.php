<?php
return [
    'navigation' => [
        'group' => 'إدارة المستخدمين',
        'label' => 'الفروع',
        'plural_label' => 'الفروع',
        'model_label' => 'فرع',
    ],
    'breadcrumbs' => [
        'index' => 'الفروع',
        'create' => 'إضافة فرع',
        'edit' => 'تعديل الفرع',
    ],
    'fields' => [
        'name' => [
            'label' => 'الاسم',
            'placeholder' => 'أدخل الاسم',
        ],
        'slug' => [
            'label' => 'المعرف (Slug)',
            'placeholder' => 'يُولّد تلقائياً من الاسم',
        ],
        'users' => [
            'label' => 'المستخدمون',
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
