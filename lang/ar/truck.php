<?php
return [
    'navigation' => [
        'group' => 'إدارة الشاحنات',
        'label' => 'الشاحنات',
        'plural_label' => 'الشاحنات',
        'model_label' => 'شاحنة',
    ],
    'breadcrumbs' => [
        'index' => 'الشاحنات',
        'create' => 'إضافة شاحنة',
        'edit' => 'تعديل شاحنة',
    ],

    'sections'=>[
        'driver_info'=>'بيانات السائق',
        'contract_info'=>'بيانات التعاقد',
        'status_info'=>'بيانات حاله الشحنة',
        'financial_info'=>'التفاصيل المالية',
    ],
    'fields' => [
        'cargo_id' => [
            'label' => 'معرف الحمولة',
            'placeholder' => 'ادخل معرف الحمولة',
        ],
        'driver_name' => [
            'label' => 'اسم السائق',
            'placeholder' => 'ادخل اسم السائق',
        ],
        'driver_phone' => [
            'label' => 'هاتف السائق',
            'placeholder' => 'ادخل هاتف السائق',
        ],
        'car_number' => [
            'label' => 'رقم السيارة',
            'placeholder' => 'ادخل رقم السيارة',
        ],
        'truck_model' => [
            'label' => 'موديل السيارة',
            'placeholder' => 'ادخل موديل السيارة',
        ],
        'pack_date' => [
            'label' => 'تاريخ التحميل',
            'placeholder' => 'اختر تاريخ التحميل',
        ],
        'contractor_id' => [
            'label' => 'المقاول',
            'placeholder' => 'ادخل المقاول',
        ],
        'company' => [
            'label' => 'الشركة',
            'placeholder' => 'ادخل الشركة',
        ],
        'company_id' => [
            'label' => 'معرف الشركة',
            'placeholder' => 'ادخل معرف الشركة',
        ],
        'to' => [
            'label' => 'المخزن',
            'placeholder' => 'ادخل الوجهة',
        ],
        'from' => [
            'label' => 'المعبر',
            'placeholder' => 'ادخل نوع المصدر',
        ],

        'arrive_date' => [
            'label' => 'تاريخ الوصول',
            'placeholder' => 'اختر تاريخ الوصول',
        ],
        'truck_status' => [
            'label' => 'حالة الشاحنة',
            'placeholder' => 'ادخل حالة الشاحنة',
        ],
        'type' => [
            'label' => 'النوع',
            'placeholder' => 'ادخل النوع',
        ],
        'is_converted' => [
            'label' => 'تم التحويل',
            'placeholder' => 'هل تم التحويل؟',
        ],
        'note' => [
            'label' => 'ملاحظة',
            'placeholder' => 'ادخل ملاحظة',
        ],

        'category'=>[
            'label' => 'نوع الشحنة',
            'placeholder'=>''
        ],

        'country'=>[
            'label' => 'بلد الشحن',
            'placeholder'=>''
        ],

        'city'=>[
            'label' => 'المدينة',
            'placeholder'=>''
        ],

        'trip_days'=>[
            'label' => 'عدد ايام الرحلة',
            'placeholder'=>''
        ],
        'agreed_duration'=>[
            'label' => 'الايام المتفق عليها',
            'placeholder'=>''
        ],
        'diff_trip'=>[
            'label' => 'الفرق بين الايام',
            'placeholder'=>''
        ],
        'delay_day_value'=>[
            'label' => 'قيمة يوم التاخير',
            'placeholder'=>''
        ],
        'truck_fare'=>[
            'label' => 'اجرة الشاحة',
            'placeholder'=>'',
            'helper_text'=>'( النولون )'
        ],
        'delay_value'=>[
            'label' => 'قيمة التاخير',
            'placeholder'=>'',
            'helper_text'=>'( العطلات )'
        ],
        'total_amount'=>[
            'label' => 'المبلغ الكلي',
            'placeholder'=>''
        ],


    ],
     'filters' => [

        'toStore' => [

            'label' => 'حسب المخزن'
        ],
        'country' => [

            'label' => 'حسب بلد الانتاج'
        ],
        'pack_date' => [

            'label' => 'حسب تاريخ الشحن ',

        ],

        'arrive_date' => [

            'label' => 'حسب تاريخ الوصول ',

        ]
    ]
];
