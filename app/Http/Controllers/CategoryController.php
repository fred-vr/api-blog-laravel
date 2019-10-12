<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Category;

class CategoryController extends Controller
{

    public function __construct()
    {
        $this->middleware('api.auth', ['except' => ['index', 'show']]);
    }

    public function index()
    {
        $categories = Category::all();

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'categories' => $categories,
        ]);
    }

    public function show($id)
    {
        $category = Category::find($id);

        if (is_object($category)) {
            $data = array(
                'code' => 200,
                'status' => 'success',
                'categories' => $category,
            );
        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'la categoria no existe',
            );
        }
        return response()->json($data, $data['code']);
    }


    public function store(Request $request)
    {
        //recoger los datos por post
        $json = $request->input('json', null);
        $param_array = json_decode($json, true);


        if ($param_array) {

            //validar la categoria
            $validate = \Validator::make($param_array, [
                'name' => 'requered'
            ]);

            if ($validate->fails()) {
                $data = [
                    'code' => 400,
                    'status' => 'error',
                    'nassage' => 'No se ha guardado una categoria'
                ];
            } else {
                $category = new Category();
                $category->name = $param_array['name'];
                $category->save();

                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'category' => $category
                ];
            }
        } else {
            $data = [
                'code' => 400,
                'status' => 'error',
                'nassage' => 'No se ha enviado ninguna categoria'
            ];
        }
        return response()->json($data, $data['code']);
    }


    public function update($id, Request $request)
    {
        $json = $request->input('json', true);
        $param_array = json_decode($json, true);

        if(!empty($param_array)){

            $validate = \Validator::make($param_array, [
                'name' => 'required'
            ]);

            unset($param_array['id']);
            unset($param_array['created_at']);

            $category = Category::where('id', $id)->update($param_array);

            $data = [
                'code' => 200,
                'status' => 'success',
                'category' => $param_array
            ];

        }else{
            $data = [
                'code' => 400,
                'status' => 'error',
                'nassage' => 'No se ha guardado una categoria'
            ];
        }

        return response()->json($data, $data['code']);
    }
}
