<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Securefile extends Model
{
    protected $table = 'securefile';
    protected $fillable = [
        'title',
        'file_burn_after_read',
        'file_uid',
        'file_detail',
        'setting_id',
        'thumbnail',
    //    'size',
    ];

    // protected function fileDetail(): Attribute //productName == product_name
    // {
    //     return Attribute::make(
    //         get: fn (string $value) => basename($value),
    //     );
    // }
    // protected function size(): Attribute //productName == product_name
    // {
    //     return new Attribute(
    //         get: fn () => Storage::disk('public')->size($this->file_detail)
    //     );
    // }

    public function files_settings()
    {
        return $this->belongsTo(FilesSettings::class);
    }
}
