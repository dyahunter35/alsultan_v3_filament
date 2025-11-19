<?php
return [
    'navigation' => [
        'group' => 'إدارة المستخدمين',
        'label' => 'العملاء',
        'plural_label' => 'العملاء',
        'model_label' => 'عميل',
        'search_key' => 'اسم العميل'
    ],
    'breadcrumbs' => [
        'index' => 'العملاء',
        'create' => 'إضافة عميل',
        'edit' => 'تعديل عميل',
    ],
    'fields' => [
        'name' => [
            'label' => 'الاسم',
            'placeholder' => 'أدخل اسم العميل',
        ],
        'email' => [
            'label' => 'البريد الإلكتروني',
            'placeholder' => 'أدخل البريد الإلكتروني للعميل',
        ],
        'phone' => [
            'label' => 'رقم الهاتف',
            'placeholder' => 'أدخل رقم الهاتف',
        ],
        'permanent' => [
            'label' => 'نوع العميل',
            'placeholder' => 'اختر نوع العميل',
        ],
        'photo' => [
            'label' => 'الصورة',
            'placeholder' => 'رفع صورة العميل',
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
    ],
    'widgets' => [
        'stats' => [
            'label' => 'تقرير العميل المالي',
            'count' => 'إجمالي العملاء',
            'active' => 'العملاء النشطين',
            'inactive' => 'العملاء غير النشطين',
        ],
    ],
    'actions' => [
        'refresh' => [
            'label' => 'تحديث الأرصدة',
        ],
    ],
    'notifications' => [
        'refresh_success' => [
            'title' => 'تم تحديث أرصدة العملاء بنجاح.',
        ],
    ],
    'reports' => [
        'ledger' => [
            'title' => 'كشف حساب العملاء',
            'title_for' => 'كشف حساب العميل :customer',
        ],
    ],

    'guest_suffix' => ' (ضيف)',
];
