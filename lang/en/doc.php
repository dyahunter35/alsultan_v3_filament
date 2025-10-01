<?php
return [
    'label' => [
        'plural' => 'Documents',
        'single' => 'document',
    ],
   
    'fields' => [
       
        'name' => [
            'label' => 'Document Name',
            'placeholder' => 'Enter document name',
        ],
        'issuance_date' => [
            'label' => 'Issuance Date',
            'placeholder' => 'Enter issuance date',
        ],
        'type' => [
            'label' => 'Document Type',
            'placeholder' => 'Enter document type',
        ],

        'file' => [
            'label' => 'File',
            'placeholder' => 'Upload file',
        ],
        'created_at' => [
            'label' => 'Created At',
        ],
        'updated_at' => [
            'label' => 'Updated At',
        ],
    ],
    'filters' => [
        'issuance_date' => [
            'label' => 'Filter by Issuance Date',
        ],
    ],
    'actions' => [
        'edit' => 'تعديل',
        'delete' => 'حذف',
    ],
];