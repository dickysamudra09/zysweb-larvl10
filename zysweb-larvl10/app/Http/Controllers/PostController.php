<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function index(): View {
        $posts = Post::latest()->paginate(5);

        //render view with posts
        return view('posts.index', compact('posts'));
    }

    public function create(): View {
        return view('posts.create');
    }

    public function store(Request $request): RedirectResponse {
        $this->validate($request, [
            'image'   => 'required|image|mimes:jpeg,jpg,png|max:2048',
            'title'   => 'required|min:5',
            'content' => 'required|min:10',
        ]);

        $image = $request->file('image');
        $image->storeAs('public/posts', $image->hashName());

        Post::create([
            'image'   => $image->hashName(),
            'title'   => $request->title,
            'content' => $request->content,
        ]);

        return redirect()->route('posts.index')->with(['success' => 'Data saved successfully']);
    }

    public function show(string $id): View{
        $post = Post::findorFail($id);
        return View('posts.show', compact('post'));
    }

    public function edit(string $id): View{
        $post = Post::findorFail($id);
        return View('posts.edit', compact('post'));
    }

    public function update(Request $request, $id): RedirectResponse{
        $this->validate($request, [
            'image'     => 'image|mimes:jpeg,jpg,png|max:2048',
            'title'     => 'required|min:5',
            'content'   => 'required|min:10'  
        ]);

        $post = Post::findorFail($id);
        
        if ($request->hasFile('image')) {

            //upload new image
            $image = $request->file('image');
            $image->storeAs('public/posts', $image->hashName());

            //delete old image
            Storage::delete('public/posts/'.$post->image);

            //update post with new image
            $post->update([
                'image'     => $image->hashName(),
                'title'     => $request->title,
                'content'   => $request->content
            ]);

        } else {

            //update post without image
            $post->update([
                'title'     => $request->title,
                'content'   => $request->content
            ]);
        }

        //redirect to index
        return redirect()->route('posts.index')->with(['success' => 'Data updated successfully']);
    }

    public function destroy($id): RedirectResponse {
        $post = Post::findorFail($id);
        Storage::delete('public/posts/'. $post->image);

        $post->delete();

        return redirect()->route('posts.index')->with(['success' => 'Data deleted successfully']);
    }
}
