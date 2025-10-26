<?php
return [
    'navigation' => [
        'group' => 'المالية',
        'label' => 'المنصرفات',
        'plural_label' => 'المنصرفات',
        'model_label' => 'منصرف',
        'icon' => 'heroicon-m-currency-dollar',
    ],
    'breadcrumbs' => [
        'index' => 'المنصرفات',
        'create' => 'إضافة منصرف',
        'edit' => 'تعديل منصرف',
    ],
    'tabs' => [
        'currency' => [
            'label' => 'العملة / التحويل',
            'icon' => 'heroicon-o-user',
        ],
        'store' => 'منصرفات المخازن',
        'financial' => 'المعاملات المالية',
        'customs' => 'الجمارك',
        'papers' => 'شهادات الوارد',
        'tax' => 'الضرائب',
        'government' => 'الرسوم الحكومية',
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
            'label' => 'طريقة الدفع',
            'placeholder' => 'اختر طريقة الدفع',
        ],
        'payed_serial' => [
            'label' => 'رقم الإشعار',
            'placeholder' => 'أدخل رقم الإشعار',
        ],
        'quantity' => [
            'label' => 'المبلغ',
            'placeholder' => 'أدخل المبلغ',
        ],
        'price' => [
            'label' => 'السعر',
            'placeholder' => 'أدخل السعر',
        ],
        'formatted_quantity' => [
            'label' => 'المبلغ كتابةً',
        ],
        'statement' => [
            'label' => 'الوصف / الملاحظات',
            'placeholder' => 'أدخل الملاحظات',
        ],
        'payed' => [
            'label' => 'نوع الدفع',
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
            'label' => 'الإجمالي',
        ],
        'total_price' => [
            'label' => 'الرصيد المتبقي',
        ],
        'tax_number' => [
            'label' => 'رقم الضريبة',
            'placeholder' => 'أدخل رقم الضريبة',
        ],
        'tax_amount' => [
            'label' => 'قيمة الضريبة',
            'placeholder' => 'أدخل قيمة الضريبة',
        ],
        'gov_fee_type' => [
            'label' => 'نوع الرسوم',
            'placeholder' => 'أدخل نوع الرسوم',
        ],
        'gov_fee_amount' => [
            'label' => 'قيمة الرسوم',
            'placeholder' => 'أدخل قيمة الرسوم',
        ],
        'created_at' => [
            'label' => 'التاريخ',
            'placeholder' => 'اختر التاريخ',
        ],
    ],
];
