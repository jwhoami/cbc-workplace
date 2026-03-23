<?php

return [

  'title' => config('app.name'),

  'heading' => 'Acceder',

  'buttons' => [

    'submit' => [
      'label' => 'Acceder',
    ],

  ],

  'fields' => [

    'username' => [
      'label' => 'Usuario',
    ],

    'email' => [
      'label' => 'Correo electrónico',
    ],

    'password' => [
      'label' => 'Contraseña',
    ],

    'remember' => [
      'label' => 'Acuerdame',
    ],

  ],

  'messages' => [
    'failed' => 'Credenciales invalidos.',
    'throttled' => 'Demasiados intentos. Intente de nuevo en :seconds segundos.',
  ],

];
