<?php

namespace App\Helpers;

use Firebase\JWT\JWT; //Con esto puedo utilizar todos los métodos de esta libreria
use Illuminate\Support\Facades\DB; //Con esto hago consusltas a la base de datos
use App\User;
//use Illuminate\Support\Collection;

class JwtAuth{
    
    public $key; //Atributo

    public  function __construct(){
        $this->key = "esto_es_una_clave_super_secreta-8787"; 
    }

    public function signup($email, $password, $getToken = null){ //Este metodo sirve para autenticar al user y devolver el token
                                                //Pero tambien para devolver el user identificado
        
        // Buscar si existe el usuario con las credenciales
        $user = User::where([       //Si las credenciales son incorrectas nunca 
            'email' => $email,      //encontrara un usuario, por tanto la variable user no sera un objeto
            'password' => $password
        ])->first();

        //Comprobar si son correctas(objeto)
        $signup = false;

        if(is_object($user)){ //Si el usuario es un objeto
            $signup = true;
        }

        //Generar el token del usuario identificado
        if($signup){
            $token = array(
                'sub' => $user->id, //Aqui hace referencia al id del usuario
                'email' => $user->email,
                'name' => $user->name,
                'surname' => $user->surname,
                'description' =>$user->description,
                'image' => $user->image,
                'iat'  =>   time(), //Esto sirve para saber cuando se creo el token
                'exp' => time() + (7*24*60*60)
            );

            $jwt = JWT::encode($token, $this->key, 'HS256'); /*Primer parametro: la variable que quiero encriptar, 
                                            segundo parametro: la clave de seguridad */
                                            
            $decoded =  JWT::decode($jwt, $this->key, ['HS256']); /* Ojo JWT  no acepta sha256*/

            //Devolver los datos decodificados o el token, en funcion de un parametro    
            if(is_null($getToken)){
                $data = $jwt;
            }else{
                $data=$decoded;
            }

        }else{
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'Login incorrecto'
            );
        }

        
        return $data;
    }    


     //Esta funcion chequea si el token es correcto
    public function checkToken($jwt, $getIdentity = false){
        $auth=false;
            try{
                $jwt = str_replace('"','',$jwt);
                $decoded= JWT::decode($jwt,$this->key,['HS256']);
            }catch (\UnexpectedValueException $e){
                $auth=false;
            }
            catch (\DomainException $e){
                $auth=false;
            }
            if(!empty($decoded) && is_object($decoded) && isset($decoded->sub)){
                $auth= true;
            }
            else{
                $auth=false;
            }
            if($getIdentity){
                return $decoded;
            }
            return $auth;
       
    }


    
}
