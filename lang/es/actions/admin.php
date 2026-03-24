<?php

return [
    'membership-approval' => [
        'label' => 'Aprobación',

        'form' => [
            'decision' => 'Decisión',
        ],

        'exceptions' => [
            'invalid-state' => 'El estado no es válido',
        ],
    ],

    'approve-venture-request' => [
        'label' => 'Aprobación',

        'form' => [
            'decision' => 'Decisión',
        ],

        'exceptions' => [
            'invalid-state' => 'El estado no es válido',
        ],
    ],

    'reject-venture-approval' => [
        'label' => 'Desaprobar',
    ],

    'organization-verification' => [
        'verify' => [
            'label' => 'Verificar',
            'description' => 'Verificar esta organización',
            'success' => 'Organización verificada exitosamente',
        ],
        'suspend' => [
            'label' => 'Suspender',
            'description' => 'Suspender esta organización',
            'success' => 'Organización suspendida',
        ],
        'form' => [
            'verification_reason' => 'Motivo',
        ],
        'exceptions' => [
            'invalid-decision' => 'La decisión de verificación no es válida',
        ],
    ],
];
