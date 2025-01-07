<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class FilesSettings extends Model
{
    protected $fillable = [
        'id',
        'expiry_date',
        'burn_after_read',
        'uid',
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
    public function securefile()
    {
        return $this->hasMany(Securefile::class);
    }
    public function securetext()
    {
        return $this->hasMany(Securetext::class);
    }
}
