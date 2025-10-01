<?php
return [
    'navigation' => [
        'group' => 'المالية',
        'label' => 'المنصرفات',
        'plural_label' => 'المنصرفات',
        'model_label' => 'منصرف',
    ],
    'breadcrumbs' => [
        'index' => 'المنصرفات',
        'create' => 'إضافة منصرف',
        'edit' => 'تعديل منصرف',
    ],
    'tabs' => [
        'currency' => [
            'label' => 'العملة/التحويل',
            'icon' => 'heroicon-o-user'
        ],
        'store' => 'منصرفات مخازن',
        'financial' => 'معاملات مالية',
        'customs' => 'جمارك',
        'papers' => 'شهادات وارد',
        'tax' => 'ضرائب',
        'government' => 'رسوم حكومية',
    ],
    'fields' => [
        'exp_type' => [
            'label' => 'نوع المنصرف',
            'placeholder' => 'اختر نوع المنصرف',
        ],
        'user_id' => [
            'label' => 'عميل العملة',
            'placeholder' => 'اختر العميل',
        ],
        'from_id' => [
            'label' => 'من',
            'placeholder' => 'اختر المصدر',
        ],
        'rep_id' => [
            'label' => 'المندوب',
            'placeholder' => 'اختر المندوب',
        ],
        'payed_method' => [
            'label' => 'وسيلة الدفع',
            'placeholder' => 'اختر وسيلة الدفع',
        ],
        'payed_serial' => [
            'label' => 'رقم الاشعار',
            'placeholder' => 'ادخل رقم الاشعار',
        ],
        'quantity' => [
            'label' => 'المبلغ المراد تحويله',
            'placeholder' => 'ادخل المبلغ',
        ],
        'price' => [
            'label' => 'السعر',
            'placeholder' => 'ادخل السعر',
        ],
        'formatted_quantity' => [
            'label' => 'المبلغ كتابة',
        ],
        'statement' => [
            'label' => 'ملاحظات',
            'placeholder' => 'ادخل الملاحظات',
        ],
        'payed' => [
            'label' => 'طريقة الدفع',
            'options' => [
                'urgent' => 'عاجل',
                'delayed' => 'مؤجل',
            ],
        ],
        'store_id' => [
            'label' => 'المخزن',
            'placeholder' => 'اختر المخزن',
        ],
        'total' => [
            'label' => 'الصافي',
        ],
        'total_price' => [
            'label' => 'المتبقي',
        ],
        'tax_number' => [
            'label' => 'رقم الضريبة',
            'placeholder' => 'ادخل رقم الضريبة',
        ],
        'tax_amount' => [
            'label' => 'قيمة الضريبة',
            'placeholder' => 'ادخل قيمة الضريبة',
        ],
        'gov_fee_type' => [
            'label' => 'نوع الرسوم',
            'placeholder' => 'ادخل نوع الرسوم',
        ],
        'gov_fee_amount' => [
            'label' => 'قيمة الرسوم',
            'placeholder' => 'ادخل قيمة الرسوم',
        ],
        'created_at' => [
            'label' => 'التاريخ',
            'placeholder' => 'اختر التاريخ',
        ],
    ],
];
