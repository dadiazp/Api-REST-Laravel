<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Category;

class CategoryController extends Controller
{
    //Esta funcion sirve para usar la autenticacion en algunas rutas y no en toda, recordar que estamos usando Resources
    public function __construct(){
        $this->middleware('api.auth', ['except' => ['index', 'show']]);//Utiliza el api.auth en todos los metodos excepto...
    }

    public function pruebas(Request $request){    
        return "accion de pruebas de CategoryController";
    }

    //Saca, lista todas las categorias
    public function index(){
        $categories = Category::all(); //Saca todas las categorias

        return response()->json([
           'categories' => $categories,
            'code' => 200,
            'status' => 'success'
        ]);
    }

    //Saca una categoria especifica dado un id
    public function show($id){
        $category = Category::find($id);

        if(is_object($category)){
            $data = [
                'status' => 'success',
                'code' => 200,
                'category' => $category
            ];
        }else{
            $data = [
                'status' => 'error',
                'code' => 400,
                'message' => 'La categoria no existe'
            ];
        }
    
        return response()->json($data, $data['code']);
    }

    //Funcion para guardar una categoria
    public function store(Request $request){

        //Recoger los datos por post
        $json = $request->input('json', null); //Si no recibo nada, lo pongo null
        $params_array = json_decode($json, true); //Con true lo convierto a array

        if(empty($params_array)){
            $data = [
                'code' => 400,
                'status' => 'error',
                'messagge' =>'No has enviado ninguna categoria'
            ];
        }else{
            
            //Validar los datos
             $validate = \Validator::make($params_array,[
                'name' => 'required'
             ]);

             //Guardar la categoria
             if($validate->fails()){
                $data = [
                    'code' => 400,
                    'status' => 'error',
                    'messagge' =>'No se ha guardado la categoria'
                 ];
             }else{
                 $category = new Category();
                 $category->name = $params_array['name'];
                 $category->save();

                 $data = [
                    'code' => 200,
                    'status' => 'success',
                    'category' => $category
                 ];
        }
        
        } 

        //Devolver el resultado
        return response()->json($data, $data['code']);
    }

    public function update($id, Request $request){
        
        //Recoger los datos por post
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if(!empty($params_array)){

            //Validar los datos
            $validate = \Validator::make($params_array,[
                'name' => 'required',
            ]);

            //Quitar lo que no quiero actualizar
            unset($params_array['id']);
            unset($params_array['created_at']);

            //Actualizar la categoria
            $category = Category::where('id', $id)->update($params_array);

            $data = [
                'code' => 200,
                'status' => 'success',
                'category' => $params_array 
             ];

        }else{
            $data = [
                'code' => 400,
                'status' => 'error',
                'messagge' =>'No se han enviado categorias para actualizar'
             ];
        }
        
        //Devolver la respuesta
        return response()->json($data, $data['code']);
    }
}
