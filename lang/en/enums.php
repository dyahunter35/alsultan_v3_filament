<?php

return [
    'all' => 'all',

    'country' => [
        'egypt' => 'Egypt',
        'qatar' => 'Qatar',
        'hind' => 'Hind',
        'sudan' => 'Sudan',
    ],

    'company_type' => [
        'company' => 'Company',
        'contractor' => 'Contractor',

    ],

    'truck_state' => [
        //     case OnWay = '0';
        // case reach = '1';
        // case barn = '-1';
        // case port = '-2';
        '0' => 'on way',
        '1' => 'reached',
        '-1' => 'barn',
        '-2' => 'port',
    ],
    'currency_option' => [
        'dolar' =>
        [
            'label' => 'US Dollar',
            'description' => 'United States Dollar',
        ],
        'ryal' => [
            'label' => 'Saudi Riyal',
            'description' => 'Saudi Arabian Riyal',
        ],
        'egy' => [
            'label' => 'Egyptian Pound',
            'description' => 'Egyptian Pound',
        ],
        'aed' => [
            'label' => 'UAE Dirham',
            'description' => 'United Arab Emirates Dirham',
        ],
         'inr' => [
            'label' => 'Indian Rupee',
            'description' => 'Indian Rupee',
        ],
         'qar' => [
            'label' => 'Qatari Riyal',
            'description' => 'Qatari Riyal',
        ],
        
    ]
];
