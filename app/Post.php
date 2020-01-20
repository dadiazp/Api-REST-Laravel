<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $table = 'posts';

    protected $fillable = [
        'title', 'content', 'category_id'
    ];

    //Relacion de muchos a uno, es decir, muchos post pueden pertener a un usuario

    public function user(){
        return $this->belongsTo('App\User', 'user_id'); //Saca el objeto de usuario relacionados por los user_id
    }

    //Relacion de muchos a uno, es decir, muchos post pueden pertener a una categoria

    public function category(){
        return $this->belongsTo('App\Category', 'category_id'); //Saca el objeto de usuario relacionados por los category_id
    }
}
