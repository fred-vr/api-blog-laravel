<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\User;

class UserController extends Controller
{
    public function pruebas(Request $request){
        return "Accion de purebas USER-CONTROLLER";
    }

    public function register(Request $request)
    {

//Recoger los datos del usuario por post

$json = $request->input('json',null);
$params = json_decode($json);               //objetos
$params_array = json_decode($json, true);   //array

if(!empty($params) && !empty($params_array)){

//Limpiar Datos
$params_array = array_map('trim', $params_array);


//validar datos

$validate = \Validator::make($params_array, [
    'name'      => 'required|alpha',
    'surname'   => 'required|alpha',
    'email'     => 'required|email|unique:users', //comprovar si el usuario existe (duplicado)
    'password'  => 'required'
]);

if($validate->fails()){
    //validadcion a fallado
    $data = array(
        'status' => 'error',
        'code' => 404,
        'message' => 'Usuario no se a creado',
        'errors' => $validate->errors()
    );
}else{
    //validacion correcta

//cifrar contraserña
    $pwd = hash('sha256', $params->password );


//Crear usuario

$user = new User();
$user->name = $params_array['name'];
$user->surname = $params_array['surname'];
$user->email = $params_array['email'];
$user->password = $pwd;
$user->role = 'ROLE_USER';
//guardar el usuario
    $user->save();


    $data = array(
        'status' => 'sussess',
        'code' => 200,
        'message' => 'Usuario se a creado correctamente',
        'user' => $user
    );
}
        }else{
    $data = array(
        'status' => 'error',
        'code' => 404,
        'message' => 'Los dato enviado no seon correcto'
    );
}

        return response()->json($data, $data['code']);
    }

    public function login(Request $request)
    {
        $jwtAuth = new \JwtAuth();
        $json = $request->input('json' . null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        $validate = \Validator::make($params_array, [
            'email' => 'required|email', //comprovar si el usuario existe (duplicado)
            'password' => 'required'
        ]);

        if ($validate->fails()) {
            //validadcion a fallado
            $signup = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'Usuario no se a podido identificar',
                'errors' => $validate->errors()
            );
        } else {

            $pwd = hash('sha256', $params->password);
            $signup = $jwtAuth->signup($params->email, $pwd);

            if(!empty($params->gettoken)){
                $signup = $jwtAuth->signup($params->email, $pwd, true);
            }
        }
            return response()->json($signup, 200);
        }

     public function update(Request $request)
     {
         $token = $request->header('Authorization');
         $jwtAuth = new \JwtAuth();
         $checkToken = $jwtAuth->checkToken($token);

         //recoger los datoas por posr
             $json = $request->input('json' . null);
             $params_array = json_decode($json, true);


             if ($checkToken && !empty($params_array)) {

                 //sacar usuario identificado
                 $user = $jwtAuth->checkToken($token, true);

                 // validar los datos
                 $validate = \Validator::make($params_array, [
                     'name' => 'required|alpha',
                     'surname' => 'required|alpha',
                     'email' => 'required|email|unique:users' . $user->sub //comprovar si el usuario existe (duplicado)
                 ]);

                 // quitar los campos que no quiero actiloza

                 unset($params_array['id']);
                 unset($params_array['role']);
                 unset($params_array['password']);
                 unset($params_array['created_at']);
                 unset($params_array['remember_token']);

                 // actualizar usuario en la bdd

                 $user_update = User::where('id', $user->sub)->update($params_array);

                 // devoñcer un array
                 $data = array(
                     'code' => 200,
                     'status' => 'success',
                     'user' => $user,
                     'changes' => $params_array
                 );
             } else {
                 $data = array(
                     'code' => 400,
                     'status' => 'error',
                     'message' => 'Usuario no esta identificado correctamente'
                 );
             }
             return response()->json($data, $data['code']);
         }


         public function upload(Request $request)
         {
             // reocoger los datos de la peticion
            $image = $request->file('file0');

            //validacion de imagen
             $validate = \Validator::make($request->all(), [
                 'file0' => 'required|image|mimes:jpg,png,gif'
             ]);

            // Guardar Imagen
             if(!$image || $validate->fails()) {
                 $data = array(
                     'code' => 400,
                     'status' => 'error',
                     'message' => 'Error al subir imagen'
                 );

             }else {
                 $image_name = time() . $image->getClientOriginalName();
                 \storage::disk('users')->put($image_name, \File::get($image));

                 $data = array(
                     'code' => 200,
                     'status' => 'success',
                     'image' => $image_name,
                 );
             }

             return response()->json($data, $data['code']);
         }


         public function getImage ($filename)
         {

             $isset = \Storage::disk('users')->exists($filename);

             if($isset){
                 $file = \Storage::disk('users')->get($filename);
                 return new Response($file, 200);
             }else{
                 $data = array(
                     'code' => 404,
                     'status' => 'error',
                     'message' => 'la imagen no existe'
                 );
             }
         }

         public function detail($id){
              $user = User::find($id);
              if(is_object($user)){
                  $data =array(
                        'code' => 200,
                        'status' => 'success',
                         'user' => $user
                  );
              }else{
                  $data =array(
                      'code' => 404,
                      'status' => 'error',
                      'user' => 'El usuario no existe'
                  );
              }
        return response()->json($data, $data['code']);
    }


}
