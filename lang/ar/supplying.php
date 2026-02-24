<?php

return [
    'navigation' => [
        'group' => 'إدارة المستخدمين',
        'label' => 'التوريدات',
        'plural_label' => 'التوريدات المالية',
        'model_label' => 'توريدة',
        'search_key' => 'التوريدة',
    ],
    'breadcrumbs' => [
        'index' => 'الموردين',
        'create' => 'إضافة مورد',
        'edit' => 'تعديل مورد',
    ],
    'fields' => [
        'customer' => [
            'label' => 'العميل',
        ],

        'representative' => [
            'label' => 'المندوب',
        ],
        'payment_method' => [
            'label' => 'طريقة الدفع',
            'placeholder' => 'اختر طريقة الدفع',
        ],
        'paid_amount' => [
            'label' => 'المبلغ المدفوع',
            'placeholder' => 'أدخل المبلغ المدفوع',
        ],
        'statement' => [
            'label' => 'البيان',
            'placeholder' => 'أدخل البيان',
        ],
        'payment_reference' => [
            'label' => 'رقم الاشعار',
            'placeholder' => 'أدخل مرجع الدفع',
        ],
        'total_amount' => [
            'label' => 'المبلغ الإجمالي',
            'placeholder' => 'أدخل المبلغ الإجمالي',
        ],
        'is_completed' => [
            'label' => 'مكتمل',
        ],
        'created_at' => [
            'label' => 'تاريخ الإنشاء',
            'placeholder' => '',
        ],
        'updated_at' => [
            'label' => 'آخر تعديل',
            'placeholder' => '',
        ],
        'deleted_at' => [
            'label' => 'تاريخ الحذف',
            'placeholder' => '',
        ],
        'creator' => [
            'label' => 'تم الإنشاء بواسطة',
            'placeholder' => '',
        ],
    ],
];
