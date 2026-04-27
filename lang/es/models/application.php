<?php

return [
    'label' => 'Postulación',
    'plural-label' => 'Postulaciones',

    'fields' => [
        'job_listing' => 'Oferta de Empleo',
        'organization' => 'Organización',
        'member' => 'Candidato',
        'candidate_name' => 'Nombre del Candidato',
        'candidate_email' => 'Email del Candidato',
        'cover_letter' => 'Carta de Presentación',
        'screening_answers' => 'Respuestas a Preguntas',
        'screening_question' => 'Pregunta',
        'screening_answer' => 'Respuesta',
        'cv_snapshot' => 'CV',
        'cv_snapshot_filename' => 'Archivo de CV',
        'status' => 'Estado',
        'submitted_at' => 'Fecha de Postulación',
        'last_status_changed_at' => 'Último Cambio de Estado',
        'last_status_changed_by' => 'Último Cambio Realizado Por',
        'anonymized_at' => 'Fecha de Anonimización',
        'created_at' => 'Fecha de Creación',
    ],

    'navigation' => [
        'label' => 'Mis Postulaciones',
        'group' => 'Bolsa de Trabajo',
        'admin-label' => 'Postulaciones',
    ],

    'form' => [
        'cover_letter_placeholder' => 'Cuéntale a la organización por qué eres el candidato ideal (opcional, máximo 2000 caracteres)',
        'submit' => 'Enviar Postulación',
        'cancel' => 'Cancelar',
    ],

    'validation' => [
        'cover_letter_max' => 'La carta de presentación no puede exceder los 2000 caracteres.',
        'answer_max' => 'Cada respuesta no puede exceder los 500 caracteres.',
        'answer_required' => 'Debes responder todas las preguntas requeridas.',
    ],

    'notifications' => [
        'no_profile' => 'Debes completar tu perfil de candidato antes de postularte.',
        'listing_inactive' => 'Esta oferta de empleo ya no está aceptando postulaciones.',
        'duplicate' => 'Ya tienes una postulación para esta oferta.',
        'created' => 'Postulación enviada exitosamente.',
        'invalid_transition' => 'No se puede cambiar el estado de la postulación a ese valor.',
        'status_changed' => 'Estado de postulación actualizado.',
    ],

    'snapshot' => [
        'anonymized_name' => '[Anonimizado]',
    ],

    'comments' => [
        'received' => 'Postulación recibida',
        'status_changed' => 'Cambio de estado: :from → :to',
        'anonymized' => 'Postulación anonimizada por borrado de cuenta',
    ],
];
