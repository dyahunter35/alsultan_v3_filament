<?php

return [
    'navigation' => [
        'group' => 'Product Management',
        'label' => 'Units',
        'plural_label' => 'Units',
        'model_label' => 'Unit',
        'icon' => 'heroicon-m-building-office-2',
        'sort' => 7,
    ],
    'breadcrumbs' => [
        'index' => 'Units',
        'create' => 'Add Unit',
        'edit' => 'Edit Unit',
    ],
    'fields' => [
        'name' => [
            'label' => 'Name',
            'placeholder' => 'Enter unit name',
        ],
        'description' => [
            'label' => 'Description',
            'placeholder' => 'Enter unit description',
        ],
        'created_at' => [
            'label' => 'Created At',
            'placeholder' => '',
        ],
        'updated_at' => [
            'label' => 'Last Updated',
            'placeholder' => '',
        ],
    ],
];
