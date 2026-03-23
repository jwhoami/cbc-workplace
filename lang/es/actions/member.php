<?php

return [
  'request-membership' => [
    'label' => 'Solicitar Afiliación',
    'description' => 'Ser un afiliado le permitira publicar sus emprendimientos, buscar personal y buscar empleos',
    'exceptions' => [
      'unauthenticated-user' => 'No hay usuario autenticado'
    ]
  ],

  'request-venture-approval' => [
    'label' => 'Solicitar Aprobación',
  ],

  'duplicate' => [
    'label' => 'Duplicar',
  ],

  'edit-tags' => [
    'label' => 'Editar Etiquetas',
  ],

  'edit-categories' => [
    'label' => 'Editar Categorias',
  ],

  'preview' => [
    'label' => 'Vista Previa',
  ],

  'extend-validity' => [
    'label' => 'Extender Vigencia',
    'form' => [
      'helper-text' => 'Extensión válida hasta :days dias'
    ]
  ],

  'extend' => [
    'label' => 'Extender',
  ],

  'toggle-active' => [
    'label' => 'Alternar Activo',
  ],

  'toggle-can-sponsor' => [
    'label' => 'Alternar Patrocinador',
  ],

  'request-organization-verification' => [
    'label' => 'Solicitar Verificación',
    'success' => 'Solicitud de verificación enviada exitosamente',
    'exceptions' => [
      'already-verified' => 'La organización ya está verificada',
    ],
  ],
];
