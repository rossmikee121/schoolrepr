<?php

return [
    
    /*
    |--------------------------------------------------------------------------
    | School ERP Configuration
    |--------------------------------------------------------------------------
    */
    
    'academic_year' => [
        'current' => '2025-26',
        'format' => 'Y-y', // 2025-26 format
    ],
    
    'roll_number' => [
        'format' => '{academic_year}/{program_code}/{division}/{number}',
        'padding' => 3, // 001, 002, etc.
    ],
    
    'fee' => [
        'currency' => 'INR',
        'decimal_places' => 2,
        'late_fee_grace_days' => 7,
    ],
    
    'attendance' => [
        'minimum_percentage' => 75,
        'grace_percentage' => 5,
    ],
    
    'results' => [
        'pass_percentage' => 40,
        'grace_marks' => 5,
    ],
    
    'pagination' => [
        'per_page' => 25,
        'max_per_page' => 100,
    ],
    
];
