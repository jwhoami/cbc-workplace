<?php

return [
    'label' => 'Educación',
    'plural-label' => 'Educación',

    'fields' => [
        'institution' => 'Institución',
        'degree' => 'Título / Certificación',
        'field_of_study' => 'Campo de Estudio',
        'graduation_year' => 'Año de Graduación',
        'is_in_progress' => 'En Curso',
    ],

    'form' => [
        'placeholders' => [
            'institution' => 'Ej: Universidad de Panamá',
            'degree' => 'Ej: Licenciatura en Informática',
            'field_of_study' => 'Ej: Ciencias de la Computación',
            'graduation_year' => 'Ej: 2020',
        ],
    ],

    'table' => [
        'columns' => [
            'institution' => 'Institución',
            'degree' => 'Título',
            'field_of_study' => 'Campo',
            'graduation_year' => 'Año',
            'is_in_progress' => 'En Curso',
        ],
    ],

    'notifications' => [
        'created' => 'Educación agregada exitosamente',
        'updated' => 'Educación actualizada exitosamente',
        'deleted' => 'Educación eliminada exitosamente',
    ],
];
