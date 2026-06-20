<?php

declare(strict_types=1);

return [
    'label' => 'Alerta de empleo',
    'plural-label' => 'Alertas de empleo',

    'navigation' => [
        'label' => 'Mis Alertas',
        'group' => 'Bolsa de Trabajo',
    ],

    'fields' => [
        'category' => 'Categoría',
        'city' => 'Ciudad',
        'frequency' => 'Frecuencia',
        'active' => 'Activa',
        'created_at' => 'Fecha de creación',
    ],

    'form' => [
        'category_placeholder' => 'Todas las categorías',
        'city_placeholder' => 'Cualquier ciudad',
        'submit' => 'Guardar alerta',
        'cancel' => 'Cancelar',
    ],

    'actions' => [
        'create' => 'Nueva alerta',
        'edit' => 'Editar',
        'delete' => 'Eliminar',
        'toggle_active' => 'Activar',
        'toggle_inactive' => 'Desactivar',
        'quota_exceeded' => 'Has alcanzado el máximo de :max alertas por miembro.',
        'duplicate_alert' => 'Ya tienes una alerta con los mismos criterios.',
    ],

    'notifications' => [
        'created' => 'Alerta creada exitosamente.',
        'updated' => 'Alerta actualizada exitosamente.',
        'deleted' => 'Alerta eliminada.',
        'toggled_active' => 'Alerta activada.',
        'toggled_inactive' => 'Alerta desactivada.',
    ],

    'comments' => [
        'created' => 'Alerta creada por :name',
        'edited' => 'Alerta editada por :name',
        'toggled_active' => 'Alerta activada por :name',
        'toggled_inactive' => 'Alerta desactivada por :name',
        'unsubscribed_via_link' => 'Alerta deshabilitada desde enlace de desuscripción por correo',
    ],
];
