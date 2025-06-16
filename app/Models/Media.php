<?php

namespace App\Models;

use App\Lib\Traits\HasFiles;
use App\Models\Traits\ScopeIsActive;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Media extends Model
{
  /** @use HasFactory<\Database\Factories\MediaFactory> */
  use HasFactory;
  use HasFiles, ScopeIsActive;

  protected array $fileFields = [
    'disk' => 'public',
    'file',
  ];

  protected $guarded = [];

  protected function casts(): array
  {
    return [
      'is_mobile' => 'boolean',
    ];
  }

  public function ownable(): MorphTo
  {
    return $this->morphTo();
  }
}
