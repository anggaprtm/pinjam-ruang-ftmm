<?php

return [
    'default_auth_profile' => 'service_account',

    'auth_profiles' => [
        'service_account' => [
            // Pastikan path ini mengarah ke file JSON kamu
            'credentials_json' => storage_path('app/google-calendar/service-account-credentials.json'), 
        ],
        'oauth' => [
            'credentials_json' => storage_path('app/google-calendar/oauth-credentials.json'),
            'token_json' => storage_path('app/google-calendar/oauth-token.json'),
        ],
    ],

    // Masukkan ID Kalender Sekretaris di sini (biasanya alamat email)
    'calendar_id' => '93dbfe7f13693cb27cafb0ec37d538ee11c7ac8def40063e6c163f35fb0387cb@group.calendar.google.com', 
];