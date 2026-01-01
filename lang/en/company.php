<?php
return [
    'navigation' => [
        'group' => 'Company Management',
        'label' => 'Companies',
        'plural_label' => 'Companies',
        'model_label' => 'Company',
        'icon' => 'heroicon-m-building-office-2'
    ],
    'breadcrumbs' => [
        'index' => 'Companies',
        'create' => 'Add Company',
        'edit' => 'Edit Company',
    ],
    'fields' => [
        'company_details' => [
            'label' => 'Company Details',
            'description' => 'General information about the company',
        ],
        'name' => [
            'label' => 'Company Name',
            'placeholder' => 'Enter company name',
        ],
        'currency' => [
            'label' => 'Default Currency',
        ],
        'location' => [
            'label' => 'Location',
            'placeholder' => 'Enter location',
        ],
        'type' => [
            'label' => 'Account Type',
        ],
    ]
];
