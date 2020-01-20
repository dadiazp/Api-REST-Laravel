<?php

namespace App\Http\Controllers;

use App\Helpers\JwtAuth;
use Illuminate\Http\Request;
use App\User;
use Firebase\JWT\JWT;
use Illuminate\Http\Response;

class UserController extends Controller
{
    public function pruebas(Request $request){    
        return "accion de pruebas de UserController";
    } 

    public function register(Request $request){

         //Recoger los datos de los usuarios por POST
         
         $json = $request->input('json', null); //Guarda los datos json y en caso de no existir lo pone null
         $params = json_decode($json); //Saca un objeto
         $params_array = json_decode($json, true); //Saca un array


        if(!empty($params_array) && !empty($params)){

            //Limpia los espacios
            $params_array = array_map('trim', $params_array);

            //Validar datos
            $validate = \Validator::make($params_array,[ //primer parametro los datos que quiero validar, segundo parametro las validaciones
                'name'=>'required|alpha',
                'surname'=>'required|alpha',   
                'email'=>'required|email|unique:users', //unique comprueba que el usuario no exista            
                'password'=>'required'
            ]);

            if($validate->fails()){
                $data = array(
                    'status'=>'error',
                    'code' => 404,
                    'message'=>'El usuario no se ha creado',
                    'errors'=>$validate->errors()
                );
                
            }else{

                //Cifrar la contraseña
                //$pwd = password_hash($params->password, PASSWORD_BCRYPT, ['cost' =>4]); //Esta no me sirve porque 
                                                                                        //genera cifrados distintos
                
                $pwd = hash('sha256', $params->password);

                //Crear el usuario
                $user = new User();
                $user->name = $params_array['name'];
                $user->surname = $params_array['surname'];
                $user->email = $params_array['email'];
                $user->password = $pwd;
                $user->role = 'ROLE_USER';

                //Guardar el usuario e la BD
                 $user->save();
               

                $data = array(
                    'status'=>'success',
                    'code' => 200,
                    'message'=>'El usuario  se ha creado',
                    'user' => $user
                );
            }
        }else{
            $data = array(
                'status'=>'error',
                'code' => 404,
                'message'=>'Los campos estan null',
            );
        }    

        //Esto convierte un objeto o un array en json
        return response()->json($data, $data['code']); /*El primer parametro es lo que queremos devolver
                                                            y el segundo parametro el codigo que se quiere devolver*/
    }


    public function login(Request $request){
        
        $jwtAuth = new \JwtAuth(); 

        //Recibir datos por POST
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        // Validar los datos
        $validate = \Validator::make($params_array,[ //primer parametro los datos que quiero validar, segundo parametro las validaciones  
            'email'=>'required|email',              
            'password'=>'required'
        ]);

        if($validate->fails()){
            $signup = array(
                'status'=>'error',
                'code' => 404,
                'message'=>'El usuario no se ha logueado',
                'errors'=>$validate->errors()
            );
            
        }else{
            //Cifrar la contraseña
            $pwd = hash('sha256', $params->password); 

            //Devolver token o datos
            $signup = $jwtAuth->signup($params->email, $pwd); // devuelve token

            if(!empty($params->gettoken)){
                $signup = $jwtAuth->signup($params->email, $pwd, true); //devuelve datos
            }
        }

        return response()->json($signup, 200); /* Con el parametro true devuelvo los datos del user*/  
    }

    public function update(Request $request){

       //Comprobar si el usuario esta autenticado
        $token = $request->header('Authorization'); //Asi recojo los datos de la cabezara del navegador, en este caso el token
        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);
        
        //Recoger los datos por POST
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);
        
        
        if( $checkToken && !empty($params_array)){

            //Sacar usuario identificado
            $user = $jwtAuth->checkToken($token, true); //De esta forma obtendo el usuario que esta haciendo la peticion

            //Validar los datos
            $validate = \Validator::make($params_array, [
                'name'=>'required|alpha',
                'surname'=>'required|alpha',   
                'email'=>'required|email|unique:users'.$user->sub //De esta manera si no cambio el email no habra problemas        
            ]);

            //Quitar los campos que no quiero actualizar
            unset($params_array['id']);
            unset($params_array['role']);
            unset($params_array['password']);
            unset($params_array['created_at']);
            unset($params_array['remember_token']);

            //Actualizar el usuario en la bbdd
            $user_update = User::where('id', $user->sub)->update($params_array);

            //Devolver array con datos
            $data = array(
                'code' => 200,
                'status' => 'success',
                'user' => $user,
                'changes' => $params_array
            );

        }else{
             $data = array(
                 'code' => 400,
                 'status' => 'error',
                 'message' => 'el usuario no esta identificado correctamenteee'
             );
        }
    
    
        return response()->json($data, $data['code']);
        

    }

    public function upload(Request $request){
        //Recoger datos de la peticion
        $image = $request->file('file0');

        //Validacion de imagen de Laravel
        $validate = \Validator::make($request->all(),[
            'file0' => 'required|image|mimes:jpg,jpeg,png,gif'
        ]);

        //Guardar imagen
        if(!$image || $validate->fails()){
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'error al subir imagen'
           );

        }else{
            $image_name = time().$image->getClientOriginalName(); //Este metodo devuelve el nombre de la imagen
            \Storage::disk('users')->put($image_name, \File::get($image));

            $data = array(
                'code' => 200,
                'status' => 'succes',
                'image' => $image_name
            );
            
        }
        return response()->json($data, $data['code']);
    }

    //Metodo que nos permita hacer una peticion a una url y que nos devuelva la imagen que le solicitemos
    public function getImage($filename){
        $isset = \Storage::disk('users')->exists($filename);
        
        if($isset){
            $file = \Storage::disk('users')->get($filename);
            return new Response($file, 200);
        }else{
            $data = array(
                'code' => 400,
                'status' => 'error',
                'image' => 'la imagen no existe'
            ); 
        }
        return response()->json($data, $data['code']);
    }

    //Metodo que saca la informacion de un usuario en concreto
    public function detail($id){
        $user = User::find($id);

        if(is_object($user)){
            $data = array(
                'code' => 200,
                'status' => 'success',
                'user'=> $user,
            );
        }else{
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message'=> 'El usuario no existe',
            );
        }
        
        return response()->json($data, $data['code']);
    }
}
