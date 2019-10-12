<?php
namespace App\Helpers;

use Firebase\JWT\JWT;
use Iluminate\Sopport\Facades\DB;
use App\User;


class JwtAuth{

    public $key;

    public function __construct()
    {
        $this->key = 'esto es una calve secrete-514654';
    }

    public function signup($email, $passsword, $getToken = null){

    // si exite el usuario conn las credenciasles
    $user = User::where([
        'email' => $email,
        'password' => $passsword
        ])->first();


        //comprobar si son correcto

        $signup =false;
        if(is_object($user)){
            $signup = true;
        }

    //generar el token con los datos del usuario identificado
        if($signup){
            $token = array(
               'sub' => $user->id,
               'email' => $user->email,
               'name' => $user->surname,
               'surname' => $user->surname,
               'iat' => time(),
               'exp' => time() + (7 * 24 * 60 * 60)
            );
            $jwt = JWT::encode($token, $this->key, 'HS256');
            $decoded = JWT::decode($jwt, $this->key, ['HS256']);


            //devolcer los datos decodificados o el tiken, en funcion de un parametro
        if(is_null($getToken)){
            $data = $jwt;
        }else{
            $data = $decoded;
        }

        }else{
            $data = array(
              'status' => 'error',
              'message' => 'Login incorrecto'
            );
        }

    return $data;

    }


    public function checkToken($jwt, $getIdentity = false){
        $auch =false;

        try {
            $jwt = str_replace('"','', $jwt);
            $decoded = JWT::decode($jwt, $this->key, ['SH256']);
        }catch (\UnexpectedValueException $e){
            $auch = false;
        }catch (\DomainException $e){
            $auch = false;
        }
        if(!empty($decoded) && is_object($decoded) && isset($decoded->sub)){
             $auch = true;
        }else{
            $auch = false;
        }

        if($getIdentity){
             return $decoded;
        }

        return $auch;
    }


}
