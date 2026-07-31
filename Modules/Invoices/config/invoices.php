<?php

return [
    'route_middleware' => ['web', 'auth:admin'],
    'api_middleware' => ['api', 'auth:sanctum'],

    'gdt' => [
        'base_url' => env('GDT_API_BASE_URL', 'https://hoadondientu.gdt.gov.vn/api'),
        'username' => env('GDT_API_USERNAME'),
        'password' => env('GDT_API_PASSWORD'),
        'verify_ssl' => env('GDT_API_VERIFY_SSL', true),
        'timeout' => env('GDT_API_TIMEOUT', 15),
        'token_ttl' => env('GDT_TOKEN_TTL', 36000),
        'cache_key' => env('GDT_TOKEN_CACHE_KEY', 'gdt_token'),
    ],

    'meinvoice' => [
        'base_url' => env('MEINVOICE_API_BASE_URL', 'https://api.meinvoice.vn/api/integration'),
        'token' => env('MEINVOICE_API_TOKEN'),
    ],

    'storage' => [
        'export_directory' => env('INVOICES_EXPORT_DIRECTORY', 'gdt'),
        'pdf_directory' => env('INVOICES_PDF_DIRECTORY', 'hoadon_temp'),
    ],
];
