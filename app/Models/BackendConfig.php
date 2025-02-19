<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackendConfig extends Model
{
    //
    protected $table = 'configs';
    protected $fillable = ['key', 'value'];
}
