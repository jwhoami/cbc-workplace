<?php

return [
  'label' => 'Emprendimiento',
  'plural-label' => 'Emprendimientos',

  'fields' => [
    'tags' => 'Etiquetas',
    'view_count' => 'Vistas',
    'favorite_count' => 'Favoritos',
    'member_id' => 'Afiliado',
    'title' => 'Título',
    'content' => 'Contenido',
    'approval_state' => 'Estado',
    'approval_by' => 'Aprobado por',
    'approval_at' => 'Aprobado en',
    'published_at' => 'Publicado en',
    'approval_reason' => 'Razón de la decisión',
    'expires_at' => 'Fecha Vence',
    'is_active' => 'Activo',
    'is_expired' => 'Vigente',
    'is_extendable' => 'Extendible',
    'categories' => 'Categorias',
  ],

  'resource' => [
    'form' => [
      'expiration-type' => [
        'default' => 'Indefinido',
        'custom' => 'Una vez',
      ],
    ],
    'table' => [
      'published_by' => 'Publicado por',
    ],

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
      'all' => 'Todos',
      'new' => 'Nuevos',
      'updated' => 'Actualizados',
      'approval' => 'En Aprobación',
      'approved' => 'Aprobadas',
      'rejected' => 'Rechazadas',
    ],

    'tooltips' => [
      'approval_reason' => [
        'old' => 'Respuesta de la solicitud anterior',
        'new' => '',
      ],
    ],
  ],
];
