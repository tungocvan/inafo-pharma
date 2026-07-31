<?php

return [
    'route_middleware' => ['web', 'auth:admin', 'permission:view_muasamcong,admin'],
    'api_middleware' => ['api', 'auth:sanctum'],

    'origin' => env('MUASAMCONG_ORIGIN', 'https://muasamcong.mpi.gov.vn'),
    'verify_ssl' => env('MUASAMCONG_VERIFY_SSL', true),
    'timeout' => env('MUASAMCONG_TIMEOUT', 20),
    'user_agent' => env(
        'MUASAMCONG_USER_AGENT',
        'Mozilla/5.0 (compatible; Laravel Muasamcong Module)'
    ),

    'smart_token' => env('MUASAMCONG_SMART_TOKEN'),
    'session_cookie' => env('MUASAMCONG_SESSION_COOKIE'),

    'endpoints' => [
        'pricing' => env(
            'MUASAMCONG_PRICING_ENDPOINT',
            'https://muasamcong.mpi.gov.vn/o/egp-portal-personal-page/services/smart/search_prc'
        ),
        'contractor_search' => env(
            'MUASAMCONG_CONTRACTOR_ENDPOINT',
            'https://muasamcong.mpi.gov.vn/o/egp-portal-contractor-selection-v2/services/smart/search'
        ),
    ],

    'referers' => [
        'portal' => env('MUASAMCONG_PORTAL_REFERER', 'https://muasamcong.mpi.gov.vn/'),
        'pricing' => env(
            'MUASAMCONG_PRICING_REFERER',
            'https://muasamcong.mpi.gov.vn/web/guest/profile-info?menu=bid-pricing'
        ),
    ],

    'page_size' => env('MUASAMCONG_PAGE_SIZE', 20),
];
