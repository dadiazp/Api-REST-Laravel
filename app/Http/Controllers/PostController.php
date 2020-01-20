<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Post;
use App\Helpers\JwtAuth;

class PostController extends Controller
{
    public function pruebas(Request $request){    
        return "accion de pruebas de PostController";
    }

    public function __construct(){
        $this->middleware('api.auth', ['except' => ['index', 'show', 'getImage','getPostsByUser', 'getPostsByCategory']]);
    }

    public function index(){
        $posts = Post::all()->Load('category'); //De esta forma imprimo la categoria a la que pertenece

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'posts' => $posts
        ], 200);
    }

    public function show($id){
        $post = Post::find($id)->Load('category');

        if(is_object($post)){
            $data = [
                'code' => 200,
                'status' => 'success',
                'post' => $post
            ];
        }else{
            $data = [
                'code' => 404,
                'status' => 'error',
                'messagge' => 'La entrada no existe'
            ];    
        }

        return response()->json($data, $data['code']);
    }

    public function store(Request $request){

        //Recoger datos por post
        $json = $request->input('json',null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        if(!empty($params_array)){
            
            //Conseguir el usuario identificado
            $user = $this->getIdentity($request); 

            //Validar los datos
            $validate = \Validator::make($params_array,[
                'title' =>'required',
                'content' => 'required',
                'category_id' => 'required',
                'image' => 'required'
            ]);

            if($validate->fails()){
                $data = [
                    'code' => 404,
                    'status' => 'error',
                    'messagge' => 'No se ha guardado el post, faltan datos'
                ];     
            }else{

                //Guardar el post
                $post = new Post();
                $post->user_id = $user->sub;
                $post->category_id = $params->category_id;
                $post->title = $params->title;
                $post->content = $params->content;
                $post->image = $params->image;
                $post->save();

                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'post' => $post
                ];
            }

        }else{
            $data = [
                'code' => 404,
                'status' => 'error',
                'messagge' => 'Envia los datos correctamente'
            ];
        }

        //Devolver la respuesta
        return response()->json($data, $data['code']);
    }

    public function update($id, Request $request){ //El id llegara por la url

        //Recoger los datos por post
        $json = $request->input('json', null);
        $params_array =json_decode($json, true);

        if(!empty($params_array)){

            //Validar los datos
            $validate = \Validator::make($params_array,[
                'title' => 'required',
                'content' => 'required',
                'category_id' => 'required'
            ]);

            if(!($validate->fails())){
                //Eliminar lo que no se quiere actualizar
                unset($params_array['id']);
                unset($params_array['user_id']);
                unset($params_array['created_at']);
                unset($params_array['user']);

                //Conseguir user identificado
                $user = $this->getIdentity($request);
            
                //Buscar el registro
                $post = Post::where('id',$id)
                 ->where('user_id', $user->sub)
                 ->first(); //Concatenacion de where o condicione

                 if(!empty($post) && is_object($post)){

                    //Actualizar el registro
                    $post->update($params_array);

                    //Devolver algo
                    $data = array(
                        'code' => 200,
                        'status' => 'sucess',
                        'changes' => $params_array,
                        'post' => $post
                    );
                 }else{
                    $data = [
                       'code' => 400,
                       'status' => 'error',
                       'messagge' => 'El post no pertenece a este user o no existe'
                    ]; 
               }
                /*$where = [
                    'id' => $id,
                    'user_id' => $user->sub
                ];
                 
                $post = Post::updateOrCreate($where, $params_array);
                        //Cuando se usa el updateOrCreate solo se puede usar un where, no se pueden anidar*/

        }else{
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'datos enviados incorrectamente'
            ]; 
        }

            //Devolver la respuesta
            return response()->json($data, $data['code']);
    }
}

    public function destroy($id, Request $request){

        //Conseguir user identificado
        $user = $this->getIdentity($request); 

        //Conseguir  el registro
        $post = Post::where('id',$id)
            ->where('user_id', $user->sub)
            ->first(); //Concatenacion de where o condiciones

        if(!empty($post)){
    
            //Borrarlo
            $post->delete();

            //Devolver algo
            $data = [
                'code' =>200,
                'status' => 'success',
                'post' => $post
            ];
        }else{
            $data = [
                'code' =>400,
                'status' => 'error',
                'messagge' => 'El id enviado no existe'
            ];
        }

        return response()->json($data, $data['code']); 
    }

    private function getIdentity($request){
        $jwtAuth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $user = $jwtAuth->checkToken($token, true);

        return $user;
    }

    public function upload (Request $request){

        //Recoger la imagen de la peticion
        $image = $request->file('file0');
 
        //Validar la imagen
        $validate = \Validator::make($request->all(),[
            'file0' => 'required|image|mimes:jpg,jpeg,png,gif'
        ]);

        //Guardar la imagen en un disco
        if(!$image || $validate->fails()){
            $data = [
                'code' => 400,
                'status' => 'error',
                'messagge' => 'Error al subir la imagen'
            ];
        }else{
            $image_name = time().$image->getClientOriginalName();
            \Storage::disk('images')->put($image_name, \File::get($image));

            $data = [
                'code' => 200,
                'status' => 'success',
                'image' => $image_name
            ];
        }
        //Devolver datos
        return response()->json($data, $data['code']);
    }

    public function getImage($filename){

        //Comprobar si existe el fichero
        $isset = \Storage::disk('images')->exists($filename);

        if ($isset){
            //Conseguir la imagen
            $file = \Storage::disk('images')->get($filename);

            //Devolver la imagen
            return new Response($file, 200);
        }else{
            $data = [
                'code' => 404,
                'status' => 'error',
                'messagge' => 'La imagen no existe'
            ];
        }

        //Devolver resultado
        return response()->json($data, $data['code']);
        
    }

    
    public function getPostsByCategory($id){
        $posts = Post::where('category_id', $id)->get();

        return response()->json([
            'status' => 'success',
            'posts' => $posts
        ],200);
    }

    public function getPostsByUser($id){
        $posts = Post::where('user_id',$id)->get();

        return response()->json([
            'status' => 'success',
            'posts' => $posts
        ],200);
    }
}


