<?php

return [
    'navigation' => [
        'group' => 'Company Management',
        'label' => 'Contract',
        'plural_label' => 'Contracts',
        'model_label' => 'Contract',
        'icon' => 'heroicon-m-document-duplicate'
    ],

    'breadcrumbs' => [
        'index' => 'Contracts',
        'create' => 'Add Contract',
        'edit' => 'Edit Contract',
    ],

    'sections' => [
        'contract_info' => ['label' => 'Contract Information'],
        'financial_info' => ['label' => 'Financial Details'],
        'clauses' => ['label' => 'Contract Clauses'],
        'status_info' => ['label' => 'Status & Dates'],
        'items' => ['label' => 'Items'],
        'documents' => ['label' => 'Contract Documents'],
    ],

    'fields' => [
        'title' => [
            'label' => 'Title',
            'placeholder' => 'Enter contract title',
        ],
        'company' => [
            'label' => 'Company',
            'placeholder' => 'Select related company',
        ],

        'reference_no' => [
            'label' => 'Reference Number',
            'placeholder' => 'Enter unique reference number',
        ],
        'effective_date' => [
            'label' => 'Effective Date',
            'placeholder' => 'Select effective date',
        ],
        'duration_months' => [
            'label' => 'Duration (Months)',
            'placeholder' => 'Enter contract duration in months',
            'unit' => 'month'
        ],
        'total_amount' => [
            'label' => 'Total Amount',
            'placeholder' => 'Enter total contract value',
        ],
        'scope_of_services' => [
            'label' => 'Scope of Services',
            'placeholder' => 'Describe services covered by the contract',
        ],
        'confidentiality_clause' => [
            'label' => 'Confidentiality Clause',
            'placeholder' => 'Enter confidentiality terms',
        ],
        'termination_clause' => [
            'label' => 'Termination Clause',
            'placeholder' => 'Enter termination conditions',
        ],
        'governing_law' => [
            'label' => 'Governing Law',
            'placeholder' => 'Specify governing law',
        ],
        'status' => [
            'label' => 'Status',
            'placeholder' => 'Select contract status',
            'options' => [
                'active' => 'Active',
                'completed' => 'Completed',
                'terminated' => 'Terminated',
                'pending' => 'Pending',
            ],
        ],
        'notes' => [
            'label' => 'Notes',
            'placeholder' => 'Add any remarks or notes',
        ],
        'items' => [
            'label' => 'Items',

            'fields' => [
                'description' => ['label' => 'Name', 'placeholder' => 'Enter item name'],
                'size' => ['label' => 'Size', 'placeholder' => 'Enter size'],
                'quantity' => ['label' => 'Quantity', 'placeholder' => 'Enter quantity'],
                'unit_price' => ['label' => 'Unit Price', 'placeholder' => 'Enter unit price'],
                'total_price' => ['label' => 'Total Price'],
                'weight' => ['label' => 'Weight'],
                'machine_count' => ['label' => 'Machine Count'],
                'total_weight' => ['label' => 'Total Weight'],
            ],
        ],
        'documents' => [
            'label' => 'Documents',
            'fields' => [
                'issuance_date' => ['label' => 'Issued Date', 'placeholder' => 'Select date'],
                'file_type' => ['label' => 'Type', 'placeholder' => 'Enter file type'],
                'file' => ['label' => 'Upload File'],
                'description' => ['label' => 'Description', 'placeholder' => 'Enter description'],
            ],
            'default_label' => 'Document',
            'view_file' => 'View',
            'add_button' => 'Add Document',
        ],
        'created_by' => [
            'label' => 'Created By',
            'placeholder' => 'Select user who created the contract',
        ],
        'deleted_at' => [
            'label' => 'Deleted At',
        ],
        'items_count' => [
            'label' => 'Items Count',
            'placeholder' => 'Enter total contract value',
        ],
        'documents_count' => [
            'label' => 'Documents Count',
            'placeholder' => 'Enter total contract value',
        ],
        'created_at' => [
            'label' => 'Created At',
        ],
        'updated_at' => [
            'label' => 'Last Updated',
        ],
    ],

    'widgets' => [
        'stats' => [
            'label' => 'Contract Statistics',
            'count' => 'Total Contracts',
            'active' => 'Active Contracts',
            'completed' => 'Completed Contracts',
            'terminated' => 'Terminated Contracts',
            'pending' => 'Pending Contracts',
        ],
    ],

    'signature' => [
        'authorized' => 'Authorized Signature',
        'provider' => 'Authorized Signature',
        'placeholder' => 'Provider',
    ],

    'reference_no' => 'Reference #',

];
