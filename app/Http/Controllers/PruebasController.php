<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Post; // Ya puedo usar este modelo para hacer operaciones en la BD(sacar, introducir, TODO)
use App\Category; // Ya puedo usar este modelo para hacer operaciones en la BD(sacar, introducir, TODO)

class PruebasController extends Controller
{
    public function index(){
        $titulo = 'Animales';
        $animales = ['perro', 'gato', 'tigre'];

        return view('pruebas.index', array(
            'animalesIndice' => $animales,
            'tituloIndice' => $titulo
        ));
    }

    public function testOrm(){
        
        /*$variablePost = Post::all();

        foreach($variablePost as $post){
            echo "<h1>".$post->title."</h1>";
            echo "<span style= color:grey>".$post->user->name."-".$post->category->name."</span>";
            echo "<p>".$post->content."</p>";
            echo "<hr>";
        }   */ 

        $variableCategories = Category::all(); //Vacia todos los objetos de Category en variableCategoria

        foreach($variableCategories as $category){
            echo "<h1>".$category->name."</h1>";
            
            foreach($category->posts as $post){
                echo "<h1>".$post->title."</h1>";
                echo "<span style= color:grey>".$post->user->name."-".$post->category->name."</span>";
                echo "<p>".$post->content."</p>";
            }    

            echo "<hr>";
        }
        
        die(); //Esto hace que no sea necesario de una vista
    }    
}
