<?php
return [
    'navigation' => [
        'group' => 'المالية',
        'label' => 'المعاملات بالعملات',
        'plural_label' => 'المعاملات بالعملات',
        'model_label' => 'معاملة عملة',
        'icon' => 'heroicon-m-building-office-2',
    ],
    'breadcrumbs' => [
        'index' => 'المعاملات بالعملات',
        'create' => 'إضافة معاملة عملة',
        'edit' => 'تعديل معاملة عملة',
    ],
    'fields' => [
        'currency' => [
            'label' => 'العملة',
            'placeholder' => '',
        ],
        'party' => [
            'label' => 'الجهة المستفيدة',
        ],
        'payer' => [
            'label' => 'جهه الدفع',
            'placeholder' => '',
        ],
        'party_id' => [
            'label' => 'المعرف للجهة',
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
            'label' => 'النوع',
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
];
