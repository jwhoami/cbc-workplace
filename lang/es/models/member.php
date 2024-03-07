<?php

return [
  'label' => 'Miembro',
  'plural-label' => 'Miembros',

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
  ],

  'type' => [
    'visitor' => 'Visitante',
    'member' => 'Miembro',
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
        'new' => ''
      ]
    ]
  ],

  'resource' => [
    'table' => [
      'tabs' => [
        'visitors' => 'Visitantes',
        'requests' => 'Solicitudes',
        'members' => 'Miembros'
      ]
    ],

    'sections' => [
      'membership' => [
        'label' => 'Solicitud de Membresía',
        'description' => [
          'waiting' => 'En espera de respuesta',
          'returned' => 'Solicitud respondida',
        ],
      ]
    ],
  ]
];
