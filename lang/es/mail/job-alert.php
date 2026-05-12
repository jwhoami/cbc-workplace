<?php

declare(strict_types=1);

return [
    'digest' => [
        'daily' => [
            'subject' => 'Resumen diario: :count oferta(s) que coinciden con tu alerta',
            'greeting' => 'Hola :name',
            'intro' => 'Estas son las ofertas publicadas en las últimas 24 horas que coinciden con tu alerta:',
        ],
        'weekly' => [
            'subject' => 'Resumen semanal: :count oferta(s) que coinciden con tu alerta',
            'greeting' => 'Hola :name',
            'intro' => 'Estas son las ofertas publicadas en los últimos 7 días que coinciden con tu alerta:',
        ],
    ],

    'instant' => [
        'subject' => ':count nueva(s) oferta(s) coincide(n) con tu alerta',
        'greeting' => 'Hola :name',
        'intro' => 'Acabamos de aprobar oferta(s) que coinciden con tu alerta:',
    ],

    'offer' => [
        'organization' => 'Organización',
        'category' => 'Categoría',
        'city' => 'Ciudad',
        'modality' => 'Modalidad',
        'view' => 'Ver oferta',
    ],

    'unsubscribe' => [
        'cta' => 'Cancelar suscripción',
        'confirmation_title' => 'Alerta deshabilitada',
        'confirmation_body' => 'Has cancelado la suscripción a esta alerta. No volverás a recibir correos para los criterios indicados.',
        'not_found_title' => 'Alerta no encontrada',
        'not_found_body' => 'Esta alerta no existe o ya fue cancelada previamente.',
        'back_home' => 'Volver al inicio',
        'criteria_recap' => 'Criterios de la alerta:',
    ],

    'closing' => 'Bendiciones,',
    'signature' => 'El equipo de Bolsa de Trabajo',
];
