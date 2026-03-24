<?php

namespace App\Lib\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

/*
 * Model must have
 * - a column by name "file" of type varchar
 * - or column by name "files" of type json casted as array
 * - or an array by name "fileFields" listing the columns
 *   protected array $fileFields = [
      'disk' => 'files',
      'db_col_1' => 'documents', //disk "documents" is used
      'db_col_2', //disk "files" is used
    ];
 *
 * - must be stored in a filesystem called files
 */

trait HasFiles
{
    // Define this on your model
    // protected array $fileFields = [
    //   'disk' => 'files',
    //   'file',
    // ];

    protected static function bootHasFiles(): void
    {
        static::deleting(function (Model $record) {
            $fields = $record->fileFields;
            $defaultDisk = 'public';
            if ($fields) {
                $defaultDisk = $fields['disk'] ?? 'public';
            }
            unset($fields['disk']);
            foreach ($fields as $k => $v) {
                $f = (is_numeric($k)) ? $v : $k;
                if ($record->disk ?? null) {
                    $disk = $record->disk;
                } else {
                    $disk = (is_numeric($k)) ? $defaultDisk : $v;
                }
                if (! $disk) {
                    $disk = $defaultDisk;
                }
                $files = Arr::wrap($record->{$f});
                foreach ($files as $file) {
                    Storage::disk($disk)->delete($file);
                }
            }
        });
    }
}
