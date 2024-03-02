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
    'is_member' => 'Miembro',
    'membership_approval_by' => 'Aprobado por',
    'membership_approval_at' => 'Aprobado en',
    'membership_reason' => 'Porque quieres ser un miembro?',
    'membership_approval_reason' => 'Razón',
  ],

  'type' => [
    'visitor' => 'Visitante',
    'member' => 'Miembro',
  ],

  'membership-state' => [
    'visitor' => 'Visitante',
    'pending' => 'Pendiente',
    'approved' => 'Aprobado',
    'rejected' => 'Rechazado',
  ]
];
