<?php

declare(strict_types=1);

return [
    'listing' => [
        'title' => 'Bolsa de Trabajo',
        'subtitle' => 'Encuentra oportunidades activas para servir y trabajar.',
        'result_count' => '{0} Sin resultados|{1} 1 oferta|[2,*] :count ofertas',
        'row' => [
            'published_on' => 'Publicada el :date',
            'work_mode' => 'Modalidad',
            'contract_type' => 'Contrato',
            'city' => 'Ciudad',
            'organization' => 'Organización',
        ],
        'empty' => [
            'title' => 'Por ahora no hay ofertas activas',
            'message' => 'Vuelve a revisar pronto. Las nuevas oportunidades aparecen aquí en cuanto se aprueban.',
            'with_filters' => [
                'title' => 'No encontramos ofertas con esos filtros',
                'message' => 'Intenta limpiar algún filtro o ampliar tu búsqueda.',
                'cta' => 'Limpiar filtros',
            ],
        ],
    ],

    'detail' => [
        'title' => 'Detalle de la oferta',
        'description' => 'Descripción',
        'requirements' => 'Requisitos',
        'category' => 'Categoría',
        'contract_type' => 'Tipo de contrato',
        'work_mode' => 'Modalidad',
        'location' => 'Ubicación',
        'salary' => 'Rango salarial',
        'salary_unspecified' => 'No especificado',
        'publication_date' => 'Fecha de publicación',
        'application_deadline' => 'Fecha límite para postularse',
        'organization' => 'Acerca de la organización',
        'organization_website' => 'Sitio web',
    ],

    'filters' => [
        'title' => 'Filtros',
        'category' => 'Categoría',
        'work_mode' => 'Modalidad',
        'contract' => 'Tipo de contrato',
        'city' => 'Ciudad',
        'clear_all' => 'Limpiar todos los filtros',
        'apply' => 'Aplicar',
        'sort' => [
            'label' => 'Ordenar por',
            'recent' => 'Más recientes',
            'deadline' => 'Fecha límite más próxima',
        ],
        'search_placeholder' => 'Buscar por palabra clave',
    ],

    'cta' => [
        'anonymous' => [
            'title' => '¿Te interesa esta oferta?',
            'message' => 'Inicia sesión o regístrate para postularte.',
            'sign_in' => 'Iniciar sesión',
            'register' => 'Registrarme',
        ],
        'member_no_profile' => [
            'title' => 'Casi listo para postular',
            'message' => 'Completa tu perfil de candidato para postularte a esta oferta.',
            'complete_profile' => 'Completar perfil',
        ],
        'member_candidate' => [
            'button' => 'Postularme',
        ],
    ],

    'gone' => [
        'title' => 'Esta oferta ya no está disponible',
        'message' => 'Es posible que haya expirado o que la organización la haya retirado.',
        'cta' => 'Ver otras ofertas activas',
    ],

    'not_found' => [
        'title' => 'No encontramos esta oferta',
        'message' => 'La dirección que abriste no coincide con ninguna oferta del sistema.',
        'cta' => 'Volver a la bolsa',
    ],

    'error' => [
        'title' => 'No pudimos cargar los resultados',
        'message' => 'Estamos teniendo un problema técnico. Por favor intenta de nuevo en unos segundos.',
        'retry' => 'Reintentar',
    ],

    'too_many_requests' => [
        'title' => 'Demasiadas solicitudes',
        'message' => 'Has hecho muchas búsquedas en poco tiempo. Espera un momento e intenta de nuevo.',
        'retry_in' => 'Intenta de nuevo en :seconds segundos.',
    ],
];
