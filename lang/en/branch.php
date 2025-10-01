<?php
return [
    'navigation' => [
        'group' => 'Users Management',
        'label' => 'Branches',
        'plural_label' => 'Branches',
        'model_label' => 'Branch',
    ],
    'breadcrumbs' => [
        'index' => 'Branches',
        'create' => 'Add Branch',
        'edit' => 'Edit Branch',
    ],
    'fields' => [
        'name' => [
            'label' => 'Name',
            'placeholder' => 'Enter name',
        ],
        'slug' => [
            'label' => 'Slug',
            'placeholder' => 'Auto-generated from name',
        ],
        'users' => [
            'label' => 'Users',
        ],

        'created_at' => [
            'label' => 'Created at',
            'placeholder' => '',
        ],
        'updated_at' => [
            'label' => 'Last modified at',
            'placeholder' => '',
        ],
    ],
    
];
