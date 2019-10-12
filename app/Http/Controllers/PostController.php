<?php

namespace App\Http\Controllers;

use App\Helpers\JwtAuth;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Post;

class PostController extends Controller
{
    public function __construct()
    {
        $this->middleware('api.auth', ['except' => [
            'index',
            'show',
            'getImage',
            'getPostsByCategory',
            'getPostByUser'
        ]]);
    }

    public function index()
    {
        $posts = Post::all()->load('categoty');

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'posts' => $posts
        ], 200);
    }

    public function show($id)
    {
        $post = Post::find($id)->load('category');

        if (is_object($post)) {
            $data = [
                'code' => 200,
                'status' => 'success',
                'posts' => $post
            ];

        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'la entrada no existe'
            ];
        }
        return \response()->json($data, $data['code']);
    }


    public function store(Request $request)
    {
        $json = $request->input('json', null);
        $params = json_decode($json);
        $param_array = json_decode($json, true);

        if (!empty($param_array)) {

            $user = $this->getIdentity($request);

            $validate = \Validator::make($param_array, [
                'title' => 'required',
                'content' => 'required',
                'category_id' => 'required',
                'image' => 'required'
            ]);
            if ($validate->fails()) {
                $data = [
                    'code' => 400,
                    'status' => 'success',
                    'message' => 'No se a guardado el post, falta datos'
                ];
            } else {
                $post = new Post();
                $post->user_id = $user->sub();
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

        } else {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'Envia los datos corrrectamente'
            ];
        }
        return response()->json($data, $data['code']);
    }


    public function update($id, Request $request)
    {
        //recoger los datos por post
        $json = $request->input(json, null);
        $param_array = \GuzzleHttp\json_decode($json, true);

        if (!empty($param_array)) {

            //validar los datos
            $validate = \Validator::make($param_array, [
                'title' => 'required',
                'content' => 'required',
                'category_id' => 'required'
            ]);


            if ($validate->fails()) {
                $data = [
                    'code' => 400,
                    'status' => 'error',
                    'nassage' => 'No se ha guardado en post'
                ];
            } else {

                //eliminar lo que no se quiere eliminar
                unset($param_array['id']);
                unset($param_array['user_id']);
                unset($param_array['created_at']);
                unset($param_array['user']);

                $user = $this->getIdentity($request);

                $post = Post::where('id', $id)
                    ->where('user_id', $user->sub)
                    ->first();

                if (!empty($post) && is_object($post)) {

                    $post = Post::updateOrCreate($param_array);
                    $data = [
                        'code' => 200,
                        'status' => 'success',
                        'post' => $param_array,
                        'change' => $post
                    ];
                }

//               $where = [
//                   'id' => $id,
//                   'user_id' => $user->sub
//               ];
//               //actualixar registro
//               $post = Post::updateOrCreate($where, $param_array);

                //devover algo

            }

        } else {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'datos enviado incorrectamente'
            ];
        }
        return response()->json($data, $data['code']);
    }

    public function destroy($id, Request $request)
    {
        $user = $this->getIdentity($request);


        //conseguir el registro
        $post = Post::where('id', $id)
            ->where('user_id', $user->sub)
            ->first();
        //Borrarlo

        if (!empty($post)) {
            $post->delete();

            //Devolver algo
            $data = [
                'code' => 200,
                'status' => 'success',
                'post' => $post
            ];

        } else {
            $data = [
                'code' => 400,
                'status' => 'error',
                'nassage' => 'El post no existe'
            ];
        }
        return response()->json($data, $data['code']);
    }

    private function getIdentity(Request $request)
    {
        $jwtAuth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $user = $jwtAuth->checkToken($token, true);

        return $user;
    }

    public function upload(Request $request)
    {
        $image = $request->file('file0');

        $valodate = \Validator::make($request->all(), [
            'file0' => 'required|image|mimes:jpg,jpeg,png,git'
        ]);

        if (!$image || $valodate->fails()) {
            $data = [
                'code' => 400,
                'status' => 'error',
                'nassage' => 'Error al subir la imagen'
            ];
        } else {
            $image_name = time() . $image->getClientOriginalName();

            \Storage::disk('images')->put($image_name, \File::get($image));

            $data = [
                'code' => 200,
                'status' => 'success',
                'image' => $image_name
            ];
        }
        return response()->json($data, $data['code']);
    }

    public function getImage($filename)
    {

        $isset = \Storage::disk('images')->exists($filename);

        if ($isset) {
            $file = \Storage::disk('images')->get($filename);
            return new Response($file, 200);

        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'nassage' => 'la imagen no existe'
            ];
        }
        return response()->json($data, $data['code']);

    }

    public function getPostsByCategory($id)
    {
        $post = Post::where('category_id', $id)->get();

        return \response()->json([
            'status' =>'success',
            'posts' => $post
            ],200);
    }

    public function getPostsByUser($id)
    {
        $post = Post::where('user_id', $id)->get();

        return \response()->json([
            'status' =>'success',
            'posts' => $post
        ],200);
    }

}
