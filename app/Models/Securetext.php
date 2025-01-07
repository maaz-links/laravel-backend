<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Crypt;
class Securetext extends Model
{
    protected $table = 'securetext';
    protected $fillable = [
        'content',
        'setting_id',
    ];

    public function files_settings()
    {
        return $this->belongsTo(FilesSettings::class);
    }

    protected function content(): Attribute //productName == product_name
    {
        return Attribute::make(
            get: fn (string $value) => Crypt::decryptString($value),
            set: fn (string $value) => Crypt::encryptString($value),
        );
    }
}
