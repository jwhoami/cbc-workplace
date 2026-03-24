<?php

return [
    'social' => [
        'networks' => [
            'X' => 'X',
            'Youtube' => 'Youtube',
            'Facebook' => 'Facebook',
            'LinkedIn' => 'LinkedIn',
            'Instagram' => 'Instagram',
        ],
    ],
    'ventures' => [
        'validity' => [
            'default' => 90,
            'maxExtension' => 90,
        ],
        'deleteExpiredVenturesAfterDays' => 30,
    ],
    'rateLimiter' => [
        'login' => 5,
        'register' => 5,
    ],
    'affiliateRole' => 'AFFILIATE',
    'affiliateImageGallery' => [
        'max' => 3,
    ],
    'invitationCodeRequiredForRegistration' => false,
];
