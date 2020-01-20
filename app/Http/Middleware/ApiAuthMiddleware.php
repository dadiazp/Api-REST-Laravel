<?php

namespace App\Http\Middleware;

use Closure;

class ApiAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //Comprobar si el usuario esta autenticado
        $token = $request->header('Authorization'); //Asi recojo los datos de la cabezara del navegador, en este caso el token
        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        if( $checkToken){
            return $next($request);
        }else{
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'el usuario no esta identificado correctamenteee'
            );
            return response()->json($data, $data['code']);
        }
    }
}
