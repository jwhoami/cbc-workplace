<?php

return [
  'fields' => [
    'id' => 'Id',
    'reason' => 'Razón',
  ],

  'actions' => [
    'create' => ['label' => 'Crear', 'tooltip' => ''],
    'goto-list' => ['label' => 'Lista', 'tooltip' => ''],
    'back' => ['label' => 'Volver', 'tooltip' => 'Volver'],
    'decision' => ['label' => 'Decisión', 'tooltip' => ''],
    'status-advance' => ['label' => 'Avanzar Estado', 'tooltip' => ''],
    'status-set' => ['label' => 'Fijar Estado', 'tooltip' => ''],
    'status-request-approval' => ['label' => 'Solicitar Aprobación', 'tooltip' => ''],
    'view' => ['label' => 'Vista', 'tooltip' => ''],
    'edit' => ['label' => 'Editar', 'tooltip' => ''],
    'delete' => ['label' => 'Borrar', 'tooltip' => ''],
    'bulk-delete' => ['label' => 'Eliminar Selección', 'tooltip' => ''],
  ],

  'enums' => [
    'venture-approval-state' => [
      'new' => 'Nuevo',
      'updated' => 'Actualizado',
      'approval' => 'Aprobación',
      'approved' => 'Aprobado',
      'rejected' => 'Rechazado',
    ],
  ],
];
