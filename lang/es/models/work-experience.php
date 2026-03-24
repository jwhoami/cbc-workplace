<?php

return [
    'label' => 'Experiencia Laboral',
    'plural-label' => 'Experiencias Laborales',

    'fields' => [
        'company' => 'Empresa / Organización',
        'position' => 'Cargo',
        'description' => 'Descripción',
        'start_date' => 'Fecha de Inicio',
        'end_date' => 'Fecha de Fin',
        'is_current' => 'Trabajo Actual',
    ],

    'form' => [
        'placeholders' => [
            'company' => 'Ej: Tech Corp Panamá',
            'position' => 'Ej: Desarrollador Senior',
            'description' => 'Describa sus funciones y logros',
        ],
    ],

    'table' => [
        'columns' => [
            'company' => 'Empresa',
            'position' => 'Cargo',
            'start_date' => 'Inicio',
            'end_date' => 'Fin',
            'is_current' => 'Actual',
        ],
    ],

    'notifications' => [
        'created' => 'Experiencia laboral agregada exitosamente',
        'updated' => 'Experiencia laboral actualizada exitosamente',
        'deleted' => 'Experiencia laboral eliminada exitosamente',
    ],
];
