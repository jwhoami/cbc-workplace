<?php

return [
    'label' => 'Oferta de Empleo',
    'plural-label' => 'Ofertas de Empleo',

    'fields' => [
        'title' => 'Título del Puesto',
        'slug' => 'Slug',
        'description' => 'Descripción del Puesto',
        'requirements' => 'Requisitos del Puesto',
        'contract_type' => 'Tipo de Contrato',
        'work_modality' => 'Modalidad de Trabajo',
        'city' => 'Ciudad',
        'province' => 'Provincia',
        'salary_min' => 'Salario Mínimo',
        'salary_max' => 'Salario Máximo',
        'currency' => 'Moneda',
        'application_deadline' => 'Fecha Límite de Postulación',
        'state' => 'Estado',
        'screening_questions' => 'Preguntas de Selección',
        'screening_question' => 'Pregunta',
        'published_at' => 'Fecha de Publicación',
        'approval_by' => 'Aprobado Por',
        'approval_at' => 'Fecha de Aprobación',
        'approval_reason' => 'Motivo de Aprobación/Rechazo',
        'view_count' => 'Visualizaciones',
        'closed_at' => 'Fecha de Cierre',
        'organization' => 'Organización',
        'member' => 'Miembro',
        'category' => 'Categoría',
        'created_at' => 'Fecha de Creación',
    ],

    'navigation' => [
        'label' => 'Ofertas de Empleo Publicadas',
        'group' => 'Bolsa de Trabajo',
    ],

    'form' => [
        'placeholders' => [
            'title' => 'Ej: Desarrollador Full Stack',
            'description' => 'Describa las responsabilidades y tareas del puesto',
            'requirements' => 'Liste los requisitos y calificaciones necesarias',
            'city' => 'Ej: Ciudad de Panamá',
            'province' => 'Ej: Panamá',
            'screening_question' => 'Escriba una pregunta para los postulantes',
        ],
        'helpers' => [
            'salary' => 'Opcional. Si no desea publicar el salario, deje estos campos vacíos',
            'screening_questions' => 'Puede agregar hasta 5 preguntas personalizadas para los postulantes',
            'application_deadline' => 'Fecha límite para recibir postulaciones',
        ],
    ],

    'sections' => [
        'basic' => 'Información Básica',
        'details' => 'Detalles del Puesto',
        'location' => 'Ubicación',
        'salary' => 'Salario',
        'screening' => 'Preguntas de Selección',
        'approval' => 'Información de Aprobación',
        'organization_info' => 'Información de la Organización',
    ],

    'table' => [
        'columns' => [
            'title' => 'Título',
            'organization' => 'Organización',
            'category' => 'Categoría',
            'state' => 'Estado',
            'contract_type' => 'Contrato',
            'application_deadline' => 'Fecha Límite',
            'view_count' => 'Vistas',
            'created_at' => 'Creado',
        ],
    ],

    'actions' => [
        'submit_for_approval' => 'Enviar a Aprobación',
        'approve' => 'Aprobar',
        'reject' => 'Rechazar',
        'close' => 'Cerrar Oferta',
        'preview' => 'Previsualizar',
    ],

    'notifications' => [
        'created' => 'Oferta de empleo creada exitosamente',
        'updated' => 'Oferta de empleo actualizada exitosamente',
        'submitted' => 'Oferta enviada a aprobación exitosamente',
        'approved' => 'Oferta de empleo aprobada exitosamente',
        'rejected' => 'Oferta de empleo rechazada',
        'closed' => 'Oferta de empleo cerrada exitosamente',
        'cannot_edit' => 'Esta oferta no puede ser editada en su estado actual',
        'org_not_verified' => 'Su organización debe estar verificada para crear ofertas de empleo',
        'deadline_past' => 'La fecha límite de postulación debe ser una fecha futura',
    ],

    'rejection' => [
        'banner' => 'Esta oferta fue rechazada',
        'reason_label' => 'Motivo del rechazo',
    ],
];
