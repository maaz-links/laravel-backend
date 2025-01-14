<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Securemirror extends Model
{
    protected $table = 'securemirror';
    protected $fillable = ['title', 'domain'];
}
