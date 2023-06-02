<?php

namespace App\Http\Controllers;

use App\Http\Requests\Post\StoreRequest;
use App\Http\Requests\Post\UpdateRequest;
use App\Models\Post;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::all();
        return view('posts.index', compact('posts'));
    }

    public function store(StoreRequest $request)
    {
        $data = $request->validated();

        if (!empty($data['image'])) {
            $path = Storage::disk('local')->put('/images', $data['image']);
            $data['image_url'] = $path;
        }

        unset($data['image']);
        Post::create($data);
    }

    public function update(Post $post, UpdateRequest $request)
    {
        $data = $request->validated();

        if (!empty($data['image'])) {
            $path = Storage::disk('local')->put('/images', $data['image']);
            $data['image_url'] = $path;
        }

        unset($data['image']);
        $post->update($data);
    }

    public function show(Post $post)
    {
        return view('posts.show', compact('post'));
    }

    public function delete(Post $post)
    {
        $post->delete();
    }
}
