<?php

return [
    'label' => 'Nota Interna',
    'plural-label' => 'Notas Internas',

    'fields' => [
        'application' => 'Postulación',
        'author' => 'Autor',
        'body' => 'Nota',
        'created_at' => 'Fecha',
        'updated_at' => 'Última Modificación',
    ],

    'form' => [
        'body_placeholder' => 'Nota interna sobre el postulante (visible solo para la organización y administradores)',
    ],

    'validation' => [
        'body_required' => 'La nota no puede estar vacía.',
        'body_max' => 'La nota no puede exceder los 2000 caracteres.',
    ],

    'notifications' => [
        'created' => 'Nota agregada.',
        'updated' => 'Nota actualizada.',
        'deleted' => 'Nota eliminada.',
    ],

    'actions' => [
        'add' => 'Agregar Nota',
        'edit' => 'Editar Nota',
        'delete' => 'Eliminar Nota',
    ],
];
