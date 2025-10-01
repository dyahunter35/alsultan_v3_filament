<?php
return [
    'navigation' => [
        'group' => 'Truck Management',
        'label' => 'Ports',
        'plural_label' => 'Ports',
        'model_label' => 'Port',
    ],
    'breadcrumbs' => [
        'index' => 'Ports',
        'create' => 'Add Port',
        'edit' => 'Edit Port',
    ],
    'fields' => [
        'name' => [
            'label' => 'Name',
            'placeholder' => 'Enter name',
        ],
        'description' => [
            'label' => 'Description',
            'placeholder' => '',
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
