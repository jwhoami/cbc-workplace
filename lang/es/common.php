<?php

return [
  'fields' => [
    'id' => 'Id',
  ],

  'actions' => [
    'create' => ['label' => 'Crear', 'tooltip' => ''],
    'goto-list' => ['label' => 'Lista', 'tooltip' => ''],
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
    'approval-state' => [
      'undefined' => 'Por Solicitar',
      'pending' => 'Pendiente',
      'approved' => 'Aprobado',
      'rejected' => 'Rechazado',
    ]
  ]
];
