<?php
return [
    'label' => [
        'plural' => 'الحمولة',
        'single' => 'حمولة',
    ],

    'fields' => [

        'product' => [
            'label' => 'المنتج',
            'placeholder' => '',
        ],
        'size' => [
            'label' => 'المقاس',
            'placeholder' => '',
        ],
        'unit_quantity' => [
            'label' => 'كمية الوحدة',
            'placeholder' => '',
            'helper_text' => 'الكميات بالوحدة وليس الطرد',
        ],
        'real_quantity' => [
            'label' => 'الكمية الفعلية',
            'helper_text' => 'الكمية الفعلية المستلمة في المخزن ',
            'placeholder' => '',
        ],
        'quantity' => [
            'label' => 'الكمية',
            'placeholder' => '',
            'helper_text' => 'الكميات بالطرد',
        ],
        'weight' => [
            'label' => 'الوزن',
            'placeholder' => '',
        ],
        'unit_price' => [
            'label' => 'سعر الوحدة',
            'placeholder' => '',
        ],
        'note' => [
            'label' => 'ملاحظة',
            'placeholder' => '',
        ],
    ],
    'actions' => [
        'edit' => 'Edit',
        'delete' => 'Delete',
    ],
];
