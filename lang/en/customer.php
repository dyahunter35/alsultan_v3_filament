<?php

return [
    'navigation' => [
        'group' => 'Users Management',
        'label' => 'Customers',
        'plural_label' => 'Customers',
        'model_label' => 'Customer',
        'search_key' => 'Customer name',
        'sort' => 2,

    ],
    'breadcrumbs' => [
        'index' => 'Customers',
        'create' => 'Add Customer',
        'edit' => 'Edit Customer',
    ],
    'fields' => [
        'name' => [
            'label' => 'Name',
            'placeholder' => 'Enter customer name',
        ],
        'email' => [
            'label' => 'Email',
            'placeholder' => 'Enter customer email',
        ],
        'phone' => [
            'label' => 'Phone',
            'placeholder' => 'Enter phone number',
        ],
        'gender' => [
            'label' => 'Gender',
            'placeholder' => 'Select gender',
            'options' => [
                'male' => 'Male',
                'female' => 'Female',
            ],
        ],
        'permanent' => [
            'label' => 'Permanent',
            'placeholder' => 'Select permanent type',

        ],
        'photo' => [
            'label' => 'Photo',
            'placeholder' => 'Upload customer photo',
        ],
        'created_at' => [
            'label' => 'Created at',
            'placeholder' => '',
        ],
        'updated_at' => [
            'label' => 'Last modified at',
            'placeholder' => '',
        ],
        'deleted_at' => [
            'label' => 'Deleted at',
            'placeholder' => '',
        ],
    ],
    'widgets' => [
        'stats' => [
            'label' => 'Customer Finincial Report',
            'count' => 'Total Customers',
            'active' => 'Active Customers',
            'inactive' => 'Inactive Customers',
        ],
    ],
    'actions' => [
        'refresh' => [
            'label' => 'Refresh Balances',
        ],
    ],
    'notifications' => [
        'refresh_success' => [
            'title' => 'Customer balances updated successfully.',
        ],

    ],
    'reports' => [
        'ledger' => [
            'title' => 'Customer Ledger Report',
            'title_for' => 'Ledger Report for :customer',
        ],
    ],

    'guest_suffix' => ' (Guest)',
];
