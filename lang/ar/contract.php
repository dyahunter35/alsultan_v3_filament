<?php

return [
    'navigation' => [
        'group' => 'إدارة العقود',
        'label' => 'عقد',
        'plural_label' => 'العقود',
        'model_label' => 'عقد',
    ],

    'breadcrumbs' => [
        'index' => 'العقود',
        'create' => 'إضافة عقد',
        'edit' => 'تعديل عقد',
    ],

    'sections' => [
        'contract_info' => ['label' => 'معلومات العقد'],
        'financial_info' => ['label' => 'التفاصيل المالية'],
        'clauses' => ['label' => 'بنود العقد'],
        'status_info' => ['label' => 'الحالة والتواريخ'],
        'items' => ['label' => 'العناصر'],
        'documents' => ['label' => 'مستندات العقد'],
    ],

    'fields' => [
        'title' => [
            'label' => 'العنوان',
            'placeholder' => 'أدخل عنوان العقد',
        ],
        'company' => [
            'label' => 'الشركة',
            'placeholder' => 'اختر الشركة المرتبطة',
        ],
        'reference_no' => [
            'label' => 'رقم المرجع',
            'placeholder' => 'أدخل رقم مرجعي فريد',
        ],
        'effective_date' => [
            'label' => 'تاريخ السريان',
            'placeholder' => 'اختر تاريخ السريان',
        ],
        'duration_months' => [
            'label' => 'المدة (بالأشهر)',
            'placeholder' => 'أدخل مدة العقد بالأشهر',
            'unit' => 'شهر'

        ],
        'total_amount' => [
            'label' => 'إجمالي المبلغ',
            'placeholder' => 'أدخل قيمة العقد الإجمالية',
        ],
        'scope_of_services' => [
            'label' => 'نطاق الخدمات',
            'placeholder' => 'وصف الخدمات المشمولة بالعقد',
        ],
        'confidentiality_clause' => [
            'label' => 'بند السرية',
            'placeholder' => 'أدخل شروط السرية',
        ],
        'termination_clause' => [
            'label' => 'بند الإنهاء',
            'placeholder' => 'أدخل شروط الإنهاء',
        ],
        'governing_law' => [
            'label' => 'القانون المنظم',
            'placeholder' => 'حدد القانون المنظم',
        ],
        'status' => [
            'label' => 'الحالة',
            'placeholder' => 'اختر حالة العقد',
            'options' => [
                'active' => 'ساري',
                'completed' => 'مكتمل',
                'terminated' => 'منتهي',
                'pending' => 'معلق',
            ],
        ],
        'notes' => [
            'label' => 'ملاحظات',
            'placeholder' => 'أضف أي ملاحظات أو تعليقات',
        ],
        'items' => [
            'label' => 'العناصر',

            'fields' => [
                'description' => ['label' => 'الاسم', 'placeholder' => 'أدخل اسم العنصر'],
                'size' => ['label' => 'الحجم', 'placeholder' => 'أدخل الحجم'],
                'quantity' => ['label' => 'الكمية', 'placeholder' => 'أدخل الكمية'],
                'unit_price' => ['label' => 'سعر الوحدة', 'placeholder' => 'أدخل سعر الوحدة'],
                'total_price' => ['label' => 'السعر الإجمالي'],
                'weight' => ['label' => 'الوزن'],
                'total_weight' => ['label' => 'الوزن الاجمالي'],
                'machine_count' => ['label' => 'عدد المكنات'],
            ],
        ],
        'documents' => [
            'label' => 'المستندات',
            'fields' => [
                'issuance_date' => ['label' => 'تاريخ الإصدار', 'placeholder' => 'اختر التاريخ'],
                'file_type' => ['label' => 'النوع', 'placeholder' => 'أدخل نوع الملف'],
                'file' => ['label' => 'رفع ملف'],
                'description' => ['label' => 'الوصف', 'placeholder' => 'أدخل الوصف'],
            ],
            'default_label' => 'مستند',
            'view_file' => 'عرض',
            'add_button' => 'إضافة مستند',
        ],
        'items_count' => [
            'label' => 'عدد العناصر ',
            'placeholder' => 'Enter total contract value',
        ],
        'documents_count' => [
            'label' => 'عدد المستندات',
            'placeholder' => 'Enter total contract value',
        ],
        'created_by' => [
            'label' => 'أنشئ بواسطة',
            'placeholder' => 'اختر المستخدم الذي أنشأ العقد',
        ],
        'deleted_at' => [
            'label' => 'تاريخ الحذف',
        ],
        'created_at' => [
            'label' => 'تاريخ الإنشاء',
        ],
        'updated_at' => [
            'label' => 'آخر تحديث',
        ],
    ],

    'widgets' => [
        'stats' => [
            'label' => 'إحصائيات العقود',
            'count' => 'إجمالي العقود',
            'active' => 'العقود السارية',
            'completed' => 'العقود المكتملة',
            'terminated' => 'العقود المنتهية',
            'pending' => 'العقود المعلقة',
        ],
    ],

    'signature' => [
        'authorized' => 'توقيع معتمد',
        'provider' => 'توقيع معتمد',
        'placeholder' => 'المزود',
    ],

    'reference_no' => 'رقم المرجع',

];
