<?php

return [
    'navigation' => [
        'group' => 'Purchasing and Warehouse Management',
        'label' => 'Suppliers',
        'plural_label' => 'Suppliers',
        'model_label' => 'Supplier',
        'search_key' => 'Supplier Name',
    ],
    'breadcrumbs' => [
        'index' => 'Suppliers',
        'create' => 'Add Supplier',
        'edit' => 'Edit Supplier',
    ],
    'fields' => [
        'customer_id' => [
            'label' => 'Customer',
        ],

        'representative_id' => [
            'label' => 'Representative',
        ],
        'payment_method' => [
            'label' => 'Payment Method',
            'placeholder' => 'Select payment method',
        ],
        'paid_amount' => [
            'label' => 'Paid Amount',
            'placeholder' => 'Enter paid amount',
        ],
        'statement' => [
            'label' => 'Statement',
            'placeholder' => 'Enter statement',
        ],
        'payment_reference' => [
            'label' => 'Payment Reference',
            'placeholder' => 'Enter payment reference',
        ],
        'total_amount' => [
            'label' => 'Total Amount',
            'placeholder' => 'Enter total amount',
        ],
        'is_completed' => [
            'label' => 'Completed',
        ],
        'created_at' => [
            'label' => 'Created At',
            'placeholder' => '',
        ],
        'updated_at' => [
            'label' => 'Last Updated',
            'placeholder' => '',
        ],
        'deleted_at' => [
            'label' => 'Deleted At',
            'placeholder' => '',
        ],
        'creator' => [
            'label' => 'Created By',
            'placeholder' => '',
        ],
    ],
];
