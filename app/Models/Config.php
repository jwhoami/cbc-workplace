<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
  use HasFactory;

  protected $guarded = [];

  public $casts = [
    'jsondata' => 'array',
    'jsonbkup' => 'array',
  ];

  public static function make($name = "default"): Config
  {
    $config = static::query()
      ->where('name', $name)
      ->first();
    if (!is_array($config->jsondata)) {
      $config->jsondata = [];
    }
    return $config;
  }

  public function getp($key, $default = null)
  {
    $data = $this->jsondata;
    $value = data_get($data, $key) ?? $default;
    return $value;
  }

  // public function getActivitylogOptions(): LogOptions
  // {
  //   return LogOptions::defaults();
  // }
}
