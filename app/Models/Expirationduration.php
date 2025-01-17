<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expirationduration extends Model
{
    protected $table = 'expirationdurations';
    protected $fillable = ['id', 'title', 'duration'];
}
