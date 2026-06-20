<?php

return [
    'label' => 'Afiliado',
    'plural-label' => 'Afiliados',

    'fields' => [
        'type' => 'Tipo',
        'email' => 'Email',
        'name' => 'Nombre',
        'password' => 'Contraseña',
        'social_medias' => 'Redes Sociales',
        'avatar' => 'Avatar',
        'membership_state' => 'Estado',
        'membership_approval_by' => 'Revisado por',
        'membership_approval_at' => 'Revisado en',
        'membership_reason' => 'Razón de la Solicitud',
        'membership_approval_reason' => 'Detalles de la Decisión',
        'is_active' => 'Activo',
        'can_sponsor' => 'Patrocinador',
        'sponsor' => 'Invitado por',
        'created_at' => 'Fecha Registro',
    ],

    'type' => [
        'visitor' => 'Visitante',
        'member' => 'Afiliado',
    ],

    'membership-state' => [
        'undefined' => 'Visitante',
        'pending' => 'Pendiente',
        'approved' => 'Aprobado',
        'rejected' => 'Rechazado',
    ],

    'profile' => [
        'membership_approval_reason' => [
            'tooltip' => [
                'previous' => 'Respuesta de la solicitud anterior',
                'new' => '',
            ],
        ],
    ],

    'resource' => [
        'table' => [
            'tabs' => [
                'visitors' => 'Registrados',
                'requests' => 'Solicitudes',
                'members' => 'Afiliados',
            ],
        ],

        'sections' => [
            'membership' => [
                'label' => 'Solicitud de Afilición',
                'description' => [
                    'waiting' => 'En espera de respuesta',
                    'returned' => 'Solicitud respondida',
                ],
            ],
        ],
    ],
];
