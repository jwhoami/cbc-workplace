<?php

return [
    'label' => 'Perfil de Candidato',
    'plural-label' => 'Perfiles de Candidato',

    'fields' => [
        'headline' => 'Título Profesional',
        'summary' => 'Resumen Profesional',
        'city' => 'Ciudad',
        'province' => 'Provincia',
        'phone' => 'Teléfono',
        'photo' => 'Foto de Perfil',
        'cv_path' => 'Hoja de Vida (CV)',
        'faith_statement' => 'Declaración de Fe',
        'is_visible' => 'Visible',
        'member' => 'Miembro',
        'created_at' => 'Fecha de Creación',
    ],

    'navigation' => [
        'label' => 'Mi Perfil Profesional',
        'group' => 'Bolsa de Trabajo',
    ],

    'form' => [
        'placeholders' => [
            'headline' => 'Ej: Desarrollador Full Stack',
            'summary' => 'Describa su experiencia y habilidades profesionales',
            'city' => 'Ej: Ciudad de Panamá',
            'province' => 'Ej: Panamá',
            'phone' => '+507 6000-0000',
            'faith_statement' => 'Comparta su testimonio o declaración de fe (opcional)',
        ],
        'helpers' => [
            'is_visible' => 'Si está desactivado, su perfil no aparecerá en búsquedas de organizaciones',
            'cv_path' => 'Archivo PDF, máximo 5 MB',
            'photo' => 'Imagen JPG o PNG, máximo 2 MB',
        ],
    ],

    'sections' => [
        'professional' => 'Información Profesional',
        'location' => 'Ubicación y Contacto',
        'files' => 'Archivos',
        'visibility' => 'Visibilidad',
        'member_info' => 'Información del Miembro',
    ],

    'table' => [
        'columns' => [
            'member_name' => 'Miembro',
            'headline' => 'Título Profesional',
            'city' => 'Ciudad',
            'is_visible' => 'Visible',
        ],
    ],

    'notifications' => [
        'created' => 'Perfil profesional creado exitosamente',
        'updated' => 'Perfil profesional actualizado exitosamente',
    ],
];
