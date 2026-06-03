<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Web2m Payment Gateway Configuration
    |--------------------------------------------------------------------------
    */

    'partner_id' => env('WEB2M_PARTNER_ID', ''),
    'api_key' => env('WEB2M_API_KEY', ''),
    'access_token' => env('WEB2M_ACCESS_TOKEN', ''),
    'webhook_secret' => env('WEB2M_WEBHOOK_SECRET', ''),
    'api_url' => env('WEB2M_API_URL', 'https://api.web2m.com'),

    'bank_name' => env('WEB2M_BANK_NAME', 'MB-BANK'),
    'bank_code' => env('WEB2M_BANK_CODE', 'MBB'),
    'account_number' => env('WEB2M_ACCOUNT_NUMBER', '9999928071998'),
    'account_holder' => env('WEB2M_ACCOUNT_HOLDER', 'PHAM XUAN QUY'),
    'transfer_content_prefix' => env('WEB2M_TRANSFER_CONTENT_PREFIX', 'napxugetlink'),

    /*
    |--------------------------------------------------------------------------
    | Pricing Table Configuration
    |--------------------------------------------------------------------------
    | Define Xu conversion rates based on VND amounts
    */

    'pricing' => [
        [
            'amount_vnd' => 500000,
            'xu_main' => 500,
            'xu_bonus' => 100,
            'description' => 'Gói Chuyên Nghiệp',
        ],
        [
            'amount_vnd' => 200000,
            'xu_main' => 200,
            'xu_bonus' => 30,
            'description' => 'Gói Bán Chuyên',
        ],
        [
            'amount_vnd' => 100000,
            'xu_main' => 100,
            'xu_bonus' => 10,
            'description' => 'Gói Tiết Kiệm',
        ],
        [
            'amount_vnd' => 20000,
            'xu_main' => 20,
            'xu_bonus' => 0,
            'description' => 'Nạp lẻ',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Transaction Settings
    |--------------------------------------------------------------------------
    */

    'transaction_timeout' => 30,  // minutes
    'enable_logging' => true,
    'webhook_retry_attempts' => 3,
];
