<?php

namespace App\Models;

use Arr;
use DB;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class FilesSettingsView extends Model
{
    protected $table = 'files_settings';
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
        'content'
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

    protected function content(): Attribute
    {
        return new Attribute(
            get: fn () => $this->getContent($this->id)
        );
    }
    protected function getContent($id){
        if($this->type == 2){
            $content = Securefile::select('securefile.id','securefile.file_detail','securefile.thumbnail','securefile.file_uid')->where('setting_id','=',$id)->get()->toArray();
        }
        else if($this->type == 1){
            $content = Securetext::select('securetext.id','securetext.content')->where('setting_id','=',$id)->get()->toArray();
        }
        //dd(Arr::flatten($content),$content);
        return $content;
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
