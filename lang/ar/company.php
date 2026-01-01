<?php

use Filament\Support\Icons\Heroicon;

return [
    'navigation' => [
        'group' => 'إدارة الشركات',
        'label' => 'الشركات',
        'plural_label' => 'الشركات',
        'model_label' => 'شركة',
    ],
    'breadcrumbs' => [
        'index' => 'الشركات',
        'create' => 'إضافة شركة',
        'edit' => 'تعديل شركة',
    ],
    'sections' => [
        'company_details' => [
            'label' => 'تفاصيل الشركة',
            'description' => 'معلومات عامة عن الشركة',
            'icon' => 'heroicon-o-building-office-2',
        ],

    ],
    'fields' => [

        'name' => [
            'label' => 'اسم الشركة',
            'placeholder' => 'ادخل اسم الشركة',
            'icon' => 'heroicon-o-user',
            //'prefix' => 'اسم ',
            //'suffix' => 'الشركة',
        ],
        'currency' => [
            'label' => 'العملة الافتراضية',
        ],
        'location' => [
            'label' => 'الموقع',
            'placeholder' => 'ادخل الموقع',
            'icon' => Heroicon::MapPin,
        ],
        'type' => [
            'label' => 'نوع الحساب',
        ],
        'created_at' => [
            'label' => 'تاريخ الإنشاء',
            'icon' => 'heroicon-o-calendar',
        ],
        'updated_at' => [
            'label' => 'تاريخ التحديث',
            'icon' => 'heroicon-o-calendar',
        ],
    ]
];
