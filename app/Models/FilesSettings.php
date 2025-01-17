<?php

namespace App\Models;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class FilesSettings extends Model
{
    protected $fillable = [
        'id',
        'expiry_date',
        'burn_after_read',
        'uid',
        'password',
        'ip',
        'type',
        'typeintext',
        'block',
        'created_at',
    ];

    protected function typeintext(): Attribute //productName == product_name
    {
        return new Attribute(
            get: fn () => $this->getTypeToString($this->type)
        );
    }
    protected function getTypeToString($type){
        if ($type == 1){
            return "Text";
        }else if($type == 2){
            return "File";
        }else{
            return "None";
        }
    }
    protected function password(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => Crypt::decryptString($value),
            set: fn (string $value) => Crypt::encryptString($value),
        );
    }
    public function securefile()
    {
        return $this->hasMany(Securefile::class);
    }
    public function securetext()
    {
        return $this->hasMany(Securetext::class);
    }
}
