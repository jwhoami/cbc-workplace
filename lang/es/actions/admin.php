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

    'suspend-organization' => [
        'label' => 'Suspender organización',
        'description' => 'Suspende esta organización y cierra sus ofertas activas en cascada',
        'form' => [
            'reason' => 'Razón (opcional, máx 500 caracteres)',
        ],
        'notification' => [
            'success' => 'Organización suspendida. :count ofertas desactivadas en cascada.',
            'already-suspended' => 'La organización ya está suspendida.',
        ],
        'log-message' => 'Organización suspendida',
        'mail-enqueued-log' => 'Notificación de suspensión encolada',
        'mail-failed-log' => 'Notificación de suspensión falló al encolar',
        'org-comment' => 'Organización suspendida por un administrador. El motivo (si fue provisto) se encuentra en el registro de actividad.',
        'offer-deactivated-comment' => 'Oferta cerrada automáticamente porque la organización fue suspendida.',
    ],

    'reactivate-organization' => [
        'label' => 'Reactivar organización',
        'description' => 'Quita la suspensión y restablece el acceso administrativo de la organización',
        'notification' => [
            'success' => 'Organización reactivada.',
            'not-suspended' => 'La organización no está suspendida.',
        ],
        'log-message' => 'Organización reactivada',
        'org-comment' => 'La organización fue reactivada por un administrador. Las ofertas previamente cerradas en cascada no se reabren automáticamente.',
    ],
];
