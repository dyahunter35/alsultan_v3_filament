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
            'label' => ' الكمية بالوحدة',
            'placeholder' => '',
            'helper_text' => 'الكميات بالوحدة وليس الطرد',
        ],
        'real_quantity' => [
            'label' => 'الكمية الفعلية',
            'helper_text' => 'الكمية الفعلية المستلمة في المخزن ',
            'placeholder' => '',
        ],
        'quantity' => [
            'label' => 'الكمية بالطرد',
            'placeholder' => '',
        ],
        'weight' => [
            'label' => 'وزن الوحدة - جم',
            'placeholder' => '',
        ],
        'unit_price' => [
            'label' => 'سعر الوحدة',
            'placeholder' => '',
        ],
        'ton_price'=>[
            'label'=>'سعر الطن',

        ],
        'ton_weight'=>[
            'label'=>'الكمية بالطن',

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
