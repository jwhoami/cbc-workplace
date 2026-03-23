<?php

return [
  'adminMenu' => [
    'items' => [
      'config' => [
        'label' => 'Configuración',
        'url' => '/admin/configs',
        'icon' => 'heroicon-o-cog',
        'target' => '',
        'tooltip' => '',
      ],
      'role' => [
        'label' => 'Roles',
        'url' => '/admin/roles',
        'icon' => 'heroicon-o-lock-closed',
        'target' => '',
        'tooltip' => '',
      ],
      'user' => [
        'label' => 'Usuarios',
        'url' => '/admin/users',
        'icon' => 'heroicon-o-users',
        'target' => '',
        'tooltip' => '',
      ],
    ],
    'urls' => [
      [
        'label' => 'Configuración',
        'icon' => 'heroicon-o-cog',
        'url' => '/config',
        'target' => '',
      ],
      [
        'label' => 'Bitacora',
        'icon' => 'heroicon-o-film',
        'url' => '/log-viewer',
        'target' => '',
      ],
    ],
  ],

  /*
    |--------------------------------------------------------------------------
    | Broadcasting
    |--------------------------------------------------------------------------
    |
    | By uncommenting the Laravel Echo configuration, you may connect Filament
    | to any Pusher-compatible websockets server.
    |
    | This will allow your users to receive real-time notifications.
    |
    */

  'broadcasting' => [

    // 'echo' => [
    //     'broadcaster' => 'pusher',
    //     'key' => env('VITE_PUSHER_APP_KEY'),
    //     'cluster' => env('VITE_PUSHER_APP_CLUSTER'),
    //     'wsHost' => env('VITE_PUSHER_HOST'),
    //     'wsPort' => env('VITE_PUSHER_PORT'),
    //     'wssPort' => env('VITE_PUSHER_PORT'),
    //     'authEndpoint' => '/api/v1/broadcasting/auth',
    //     'disableStats' => true,
    //     'encrypted' => true,
    // ],

  ],

  /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | This is the storage disk Filament will use to put media. You may use any
    | of the disks defined in the `config/filesystems.php`.
    |
    */

  'default_filesystem_disk' => env('FILAMENT_FILESYSTEM_DISK', 'public'),

  /*
    |--------------------------------------------------------------------------
    | Assets Path
    |--------------------------------------------------------------------------
    |
    | This is the directory where Filament's assets will be published to. It
    | is relative to the `public` directory of your Laravel application.
    |
    | After changing the path, you should run `php artisan filament:assets`.
    |
    */

  'assets_path' => null,

  /*
    |--------------------------------------------------------------------------
    | Livewire Loading Delay
    |--------------------------------------------------------------------------
    |
    | This sets the delay before loading indicators appear.
    |
    | Setting this to 'none' makes indicators appear immediately, which can be
    | desirable for high-latency connections. Setting it to 'default' applies
    | Livewire's standard 200ms delay.
    |
    */

  'livewire_loading_delay' => 'default',

];
