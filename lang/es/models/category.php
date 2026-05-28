<?php

return [
    'label' => 'Categoría de Empleo',
    'plural-label' => 'Categorías de Empleo',

    'fields' => [
        'name' => 'Nombre',
        'slug' => 'Slug',
        'icon' => 'Ícono',
        'order' => 'Orden',
        'scope' => 'Ámbito',
    ],

    'navigation' => [
        'group' => 'Bolsa de Trabajo',
    ],

    'form' => [
        'placeholders' => [
            'name' => 'Ej: Tecnología e Informática',
            'slug' => 'Se genera automáticamente si se deja vacío',
            'icon' => 'Ej: heroicon-o-computer-desktop',
        ],
    ],

    'notifications' => [
        'created' => 'Categoría de empleo creada exitosamente',
        'updated' => 'Categoría de empleo actualizada exitosamente',
        'deleted' => 'Categoría de empleo eliminada exitosamente',
        'invalid_icon' => 'El ícono indicado no existe en la biblioteca de Blade Icons',
    ],
];
