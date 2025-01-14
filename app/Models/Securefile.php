<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Securefile extends Model
{
    protected $table = 'securefile';
    protected $fillable = [
        'title',
        'file_detail',
        'setting_id',
        'thumbnail',
    ];

    public function files_settings()
    {
        return $this->belongsTo(FilesSettings::class);
    }
}
