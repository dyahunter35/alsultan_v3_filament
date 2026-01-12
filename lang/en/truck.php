<?php

return [
    'navigation' => [
        'group' => 'Truck Management',
        'label' => 'Trucks',
        'plural_label' => 'Trucks',
        'model_label' => 'Truck',
        'icon' => 'heroicon-m-truck',
        'inner' => [
            'plural_label' => 'Inner Trucks',
            'model_label' => 'Inner Truck',
        ],
    ],
    'breadcrumbs' => [
        'index' => 'Trucks',
        'create' => 'Add Truck',
        'edit' => 'Edit Truck',
    ],

    'sections' => [
        'driver_info' => 'Driver Information',
        'contract_info' => 'Contract Details',
        'branch_info' => 'Warehouses',
        'status_info' => 'Shipment Status',
        'financial_info' => 'Financial Details',
    ],

    'fields' => [
        'cargo_id' => [
            'label' => 'Cargo ID',
            'placeholder' => 'Enter cargo ID',
        ],
        'code'=>[
            'label'=> 'Truck Code',
        ],
        'driver_name' => [
            'label' => 'Driver Name',
            'placeholder' => 'Enter driver name',
        ],
        'driver_phone' => [
            'label' => 'Driver Phone',
            'placeholder' => 'Enter driver phone number',
        ],
        'car_number' => [
            'label' => 'Truck Number',
            'placeholder' => 'Enter truck number',
        ],
        'truck_model' => [
            'label' => 'Truck Model',
            'placeholder' => 'Enter truck model',
        ],
        'pack_date' => [
            'label' => 'Loading Date',
            'placeholder' => 'Select loading date',
        ],
        'contractor_id' => [
            'label' => 'Contractor',
            'placeholder' => 'Enter contractor',
        ],
        'company' => [
            'label' => 'Company',
            'placeholder' => 'Enter company',
        ],
        'company_id' => [
            'label' => 'Company ID',
            'placeholder' => 'Enter company ID',
        ],
        'to' => [
            'label' => 'To Warehouse',
            'placeholder' => 'Enter destination',
        ],
        'from_branch' => [
            'label' => 'From Branch',
            'placeholder' => 'Enter source branch',
        ],
        'from' => [
            'label' => 'Port',
            'placeholder' => 'Enter source type',
        ],
        'arrive_date' => [
            'label' => 'Arrival Date',
            'placeholder' => 'Select arrival date',
        ],
        'truck_status' => [
            'label' => 'Truck Status',
            'placeholder' => 'Enter truck status',
        ],
        'type' => [
            'label' => 'Type',
            'placeholder' => 'Enter type',
        ],
        'is_converted' => [
            'label' => 'Converted',
            'placeholder' => 'Was it converted?',
        ],
        'note' => [
            'label' => 'Note',
            'placeholder' => 'Enter note',
        ],
        'category' => [
            'label' => 'Cargo Type',
            'placeholder' => '',
        ],
        'country' => [
            'label' => 'Shipping Country',
            'placeholder' => '',
        ],
        'city' => [
            'label' => 'City',
            'placeholder' => '',
        ],
        'trip_days' => [
            'label' => 'Trip Duration (Days)',
            'placeholder' => '',
        ],
        'agreed_duration' => [
            'label' => 'Agreed Duration (Days)',
            'placeholder' => '',
        ],
        'diff_trip' => [
            'label' => 'Days Difference',
            'placeholder' => '',
        ],
        'delay_day_value' => [
            'label' => 'Delay Day Value',
            'placeholder' => '',
        ],
        'truck_fare' => [
            'label' => 'Truck Fare',
            'placeholder' => '',
            'helper_text' => '(Nolon)',
        ],
        'delay_value' => [
            'label' => 'Delay Value',
            'placeholder' => '',
            'helper_text' => '(Holidays)',
        ],
        'total_amount' => [
            'label' => 'Total Amount',
            'placeholder' => '',
        ],
    ],

    'filters' => [
        'toStore' => [
            'label' => 'By Warehouse',
        ],
        'country' => [
            'label' => 'By Country of Origin',
        ],
        'pack_date' => [
            'label' => 'By Loading Date',
        ],
        'arrive_date' => [
            'label' => 'By Arrival Date',
        ],
    ],
    'actions' => [
        'create' => [
            'label' => 'Add Truck',
        ],
        'reload_cargo' => [
            'label' => 'Reload Cargo',
            'icon' => 'heroicon-m-arrow-up-tray',
            'message' => 'Are you sure you want to reload the cargo for this truck? This action will reset the truck status and related details.',
        ],
        'unload_cargo' => [
            'label' => 'Unload to Warehouse',
        ],
        'report' => [
            'label' => 'Report',
            'icon' => 'heroicon-m-document-text',
        ],
    ],
];
