<?php

return [
    'public_key' => env('EPOINT_PUBLIC_KEY', ''),
    'private_key' => env('EPOINT_PRIVATE_KEY', ''),
    
    'api_url' => env('EPOINT_API_URL', 'https://epoint.az/api/1/request'),
    'status_url' => env('EPOINT_STATUS_URL', 'https://epoint.az/api/1/get-status'),
    
    'success_url' => env('EPOINT_SUCCESS_URL', '/payment/success'),
    'error_url' => env('EPOINT_ERROR_URL', '/payment/error'),
    
    'currency' => env('EPOINT_CURRENCY', 'AZN'),
    'language' => env('EPOINT_LANGUAGE', 'az'),
    'description' => env('EPOINT_DESCRIPTION', 'Online ödəniş'),
];