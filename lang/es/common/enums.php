<?php

declare(strict_types=1);

return [
    'job-alert-frequency' => [
        'Daily' => 'Diaria',
        'Weekly' => 'Semanal',
        'Instant' => 'Instantánea',
    ],

    'dispatch-decision' => [
        'Sent' => 'Enviado',
        'SuppressedNoMatch' => 'Suprimido (sin coincidencias)',
        'SuppressedInvalidRecipient' => 'Suprimido (destinatario inválido)',
    ],

    'public-event-kind' => [
        'AlertCreated' => 'Alerta creada',
        'AlertEdited' => 'Alerta editada',
        'AlertToggled' => 'Alerta activada/desactivada',
        'AlertDeleted' => 'Alerta eliminada',
        'AlertUnsubscribedViaLink' => 'Suscripción cancelada vía enlace',
        'AlertEmailSent' => 'Correo de alerta enviado',
        'AlertEmailSuppressedNoMatch' => 'Correo de alerta suprimido (sin coincidencias)',
        'AlertEmailSuppressedInvalidRecipient' => 'Correo de alerta suprimido (destinatario inválido)',
    ],
];
