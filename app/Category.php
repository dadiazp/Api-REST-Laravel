<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categories';

    public function posts(){ /*Metodo para devolver un array con todos los post de una categoria*/
        return $this->hasMany('App\Post'); 
                            /*Esta es una relacion de una a muchas.
                             En pocas palabras es: Cuando llame a este metodo sacara todos los objetos 
                             de tipo Post que esten relacionados con la categoria especifica */
    }
}
