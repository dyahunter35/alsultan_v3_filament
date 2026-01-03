<?php

return [
    'navigation' => [
        'group' => 'إدارة المنتجات',
        'label' => 'الوحدات',
        'plural_label' => 'الوحدات',
        'model_label' => 'وحدة',
        'sort' => 7,
    ],
    'breadcrumbs' => [
        'index' => 'الوحدات',
        'create' => 'إضافة وحدة',
        'edit' => 'تعديل الوحدة',
    ],
    'fields' => [
        'name' => [
            'label' => 'الاسم',
            'placeholder' => 'ادخل اسم الوحدة',
        ],
        'description' => [
            'label' => 'الوصف',
            'placeholder' => 'ادخل وصف الوحدة',
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
