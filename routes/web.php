<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//Cargando clases
use App\Http\Middleware\ApiAuthMiddleware;

//RUTAS DE PRUEBA
Route::get('/', function () { /*Sin pasar ningun parametro, ruta sencilla*/
    return view('welcome');
});

Route::get('/prueba/{nombre?}', function($nombre = null){ /*Cuando paso parametros por la ruta*/
  $texto = '<h2>Prueba</h2>';
  $texto .= 'Nombre: '.$nombre;

  return view('pruebas', array(
      'textoIndice'=> $texto
  ));
  
});

Route::get('/animales', 'PruebasController@index'); /*Ruta que accede a un metodo de un controlador */

Route::get('/test-orm', 'PruebasController@testOrm');

//RUTAS DEL API

  //RUTAS DE PRUEBA
  /*Route::get('/usuario/pruebas', 'UserController@pruebas');

  Route::get('/categoria/pruebas', 'CategoryController@pruebas');

  Route::get('/entrada/pruebas', 'PostController@pruebas');*/

  //RUTAS DEL CONTROLADOR DE USUARIO
  Route::post('/api/register', 'UserController@register');
  Route::post('/api/login', 'UserController@login');
  Route::put('/api/user/update','UserController@update');
  Route::post('/api/user/upload', 'UserController@upload')->middleware(ApiAuthMiddleware::class);
  Route::get('/api/user/avatar/{filename}', 'UserController@getImage'); //Se pasa por la ruta la imagen
  Route::get('/api/user/detail/{id}', 'UserController@detail'); 

  //RUTAS DEL CONTROLADOR DE CATEGORIAS
  Route::resource('/api/category', 'CategoryController');

  //RUTAS DEL CONTROLADOR DE ENTRADAS
  Route::resource('api/post', 'PostController');
  Route::post('/api/post/upload', 'PostController@upload');
  Route::get('/api/post/image/{fiilename}', 'PostController@getImage');
  Route::get('/api/post/category/{id}', 'PostController@getPostsByCategory');
  Route::get('/api/post/user/{id}', 'PostController@getPostsByUser');