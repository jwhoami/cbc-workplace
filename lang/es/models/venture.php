<?php

return [
  'label' => 'Emprendimiento',
  'plural-label' => 'Emprendimientos',

  'fields' => [
    'member_id' => 'Miembro',
    'title' => 'Título',
    'content' => 'Contentido',
    'approval_state' => 'Estado',
    'approval_by' => 'Aprobado por',
    'approval_at' => 'Aprobado en',
    'approval_reason' => 'Razón de la decisión',
  ],

  'resource' => [
    'sections' => [
      'approval' => [
        'label' => 'Aprobación',
        'description' => [
          'waiting' => 'En espera de aprobación',
          'returned' => 'Solicitud respondida',
        ],
      ],
    ],

    'tabs' => [
      'all' => 'Todas',
      'undefined' => 'Elaboración',
      'pending' => 'En Aprobación',
      'approved' => 'Aprobadas',
      'rejected' => 'Rechazadas',
    ],

    'tooltips' => [
      'approval_reason' => [
        'old' => 'Respuesta de la solicitud anterior',
        'new' => ''
      ]
    ]
  ]
];
