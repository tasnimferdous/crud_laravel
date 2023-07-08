<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class PostController extends Controller
{
    //
    public function update(Post $post, Request $request){
        $incomingFields = $request->validate([
            'title' => 'required',
            'body' => 'required',
        ]);

        $incomingFields['title'] = strip_tags($incomingFields['title']);
        $incomingFields['body'] = strip_tags($incomingFields['body']);

        $post->update($incomingFields);

        return redirect('/post/'.$post->id)->with('success','Successfully updated!');
    }

    public function showUpdateForm(Post $post){
        $context = [
            'post' => $post,
        ];

        return view('edit-post', $context);
    }

    public function delete(Post $post){
        $post->delete();
        return redirect("/profile/".auth()->user()->username)->with('success','Successfully deleted!');
    }

    public function viewSinglePost(Post $post){
        $post['body'] = strip_tags(Str::markdown($post->body), '<h3><p><ul><ol><li><strong><em><br>');
        $context = [
            'post' => $post,
        ];

        return view('single-post', $context);
    }

    public function storeNewPost(Request $request){
        $incomingFields = $request->validate([
            'title' => 'required',
            'body' => 'required',
        ]);
        $incomingFields['title'] = strip_tags($incomingFields['title']);
        $incomingFields['body'] = strip_tags($incomingFields['body']);
        $incomingFields['user_id'] = auth()->id();

        $newPost = Post::create($incomingFields);

        return redirect("/post/{$newPost->id}")->with('success','Post created successfully!');
    }

    public function showCreateForm(){
        return view('create-post');
    }
}
