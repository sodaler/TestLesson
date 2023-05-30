<?php

namespace App\Http\Controllers;

use App\Http\Requests\Post\StoreRequest;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function store(StoreRequest $request)
    {
        $data = $request->validated();
        $data['image_url'] = $data['image'];
        unset($data['image']);
        Post::create($data);
    }
}
