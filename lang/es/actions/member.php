<?php

return [
  'request-membership' => [
    'label' => 'Solicitar Membresia',
    'description' => 'Ser un miembro le permitira publicar sus emprendimientos, buscar personal y buscar empleos',
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

  'extend-validity' => [
    'label' => 'Extender Vigencia',
    'form' => [
      'helper-text' => 'Extensión válida hasta :days dias'
    ]
  ],
];
