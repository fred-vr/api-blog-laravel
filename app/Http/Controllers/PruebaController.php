<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Post;
use App\Category;

class PruebaController extends Controller
{
    public function testOrm(){
        $posts = Post::all();
       // dd($posts);
            //die();
        foreach($posts as $post){
            echo $post->title;
            dd($post->Category->name);

        }

    }
}
