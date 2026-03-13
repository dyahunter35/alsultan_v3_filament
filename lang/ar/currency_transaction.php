<?php

return [
    'navigation' => [
        'group' => 'المالية',
        'label' => ' التحويلات ',
        'plural_label' => 'التحويلات ',
        'model_label' => 'تحويل عملة',
        'icon' => 'heroicon-m-building-office-2',
    ],
    'breadcrumbs' => [
        'index' => ' بالعملات',
        'create' => 'إضافة معاملة عملة',
        'edit' => 'تعديل معاملة عملة',
    ],
    'fields' => [
        'currency' => [
            'label' => 'نوع العمله',
            'placeholder' => '',
        ],
        'party' => [
            'label' => 'الجهة المستلمة',
        ],
        'payer' => [
            'label' => 'العميل',
            'placeholder' => '',
        ],
        'party_id' => [
            'label' => 'الجهة',
            'placeholder' => '',
        ],
        'amount' => [
            'label' => 'المبلغ',
            'placeholder' => 'أدخل المبلغ',
        ],
        'rate' => [
            'label' => 'سعر الصرف',
            'placeholder' => 'أدخل سعر الصرف',
        ],
        'total' => [
            'label' => 'الإجمالي',
            'placeholder' => '',
        ],
        'type' => [
            'label' => 'المعاملة',
            'placeholder' => '',
        ],
        'note' => [
            'label' => 'ملاحظات',
            'placeholder' => 'أدخل أي ملاحظات إضافية',
        ],
        'created_at' => [
            'label' => 'تاريخ الإنشاء',
            'placeholder' => '',
        ],
        'updated_at' => [
            'label' => 'آخر تحديث',
            'placeholder' => '',
        ],
        'deleted_at' => [
            'label' => 'تاريخ الحذف',
            'placeholder' => '',
        ],
    ],
    'actions' => [
        'convert' => 'شراء',
        'company_expense' => 'دفع',
        'create' => 'إضافة معاملة عملة',
        'edit' => 'تعديل معاملة عملة',
    ],
];
