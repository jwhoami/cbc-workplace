<?php

return [
    'label' => 'Organización',
    'plural-label' => 'Organizaciones',

    'fields' => [
        'legal_name' => 'Nombre Legal',
        'display_name' => 'Nombre Visible',
        'type' => 'Tipo de Entidad',
        'denomination' => 'Denominación Eclesiástica',
        'description' => 'Descripción',
        'culture_statement' => 'Declaración de Cultura Organizacional',
        'logo' => 'Logo',
        'website' => 'Sitio Web',
        'email_contact' => 'Email de Contacto',
        'phone' => 'Teléfono',
        'city' => 'Ciudad',
        'province' => 'Provincia',
        'country' => 'País',
        'verification_state' => 'Estado de Verificación',
        'verification_by' => 'Verificado Por',
        'verified_at' => 'Fecha de Verificación',
        'verification_reason' => 'Motivo',
        'is_active' => 'Activa',
        'created_at' => 'Fecha de Creación',
    ],

    'navigation' => [
        'group' => 'Bolsa de Trabajo',
    ],

    'form' => [
        'placeholders' => [
            'legal_name' => 'Ej: Iglesia Comunidad Bíblica de Cristo',
            'display_name' => 'Ej: CBC Panamá',
            'denomination' => 'Ej: Bautista, Pentecostal, No Denominacional',
            'description' => 'Describa brevemente su organización',
            'culture_statement' => 'Describa la cultura y valores de su organización',
            'website' => 'https://www.ejemplo.com',
            'email_contact' => 'contacto@ejemplo.com',
            'phone' => '+507 6000-0000',
            'city' => 'Ej: Ciudad de Panamá',
            'province' => 'Ej: Panamá',
            'country' => 'Ej: Panama',
        ],
    ],

    'sections' => [
        'general' => 'Información General',
        'contact' => 'Información de Contacto',
        'verification' => 'Verificación',
    ],

    'notifications' => [
        'created' => 'Organización creada exitosamente',
        'updated' => 'Organización actualizada exitosamente',
        'verified' => 'Organización verificada exitosamente',
        'suspended' => 'Organización suspendida',
        'verification_requested' => 'Solicitud de verificación enviada exitosamente',
    ],
];
