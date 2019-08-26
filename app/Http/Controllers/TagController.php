<?php

namespace Aleafoodapi\Http\Controllers;

use Aleafoodapi\Tag;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TagController extends Controller
{
    public function index()
    {
        $tags = Tag::orderBy('created_at', 'desc')->get();
        return $tags;
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|min:2|max:50|unique:tags',
        ]);

        $tag = new Tag;
        $tag->name = $request->name;
        $tag->save();

        return $tag;
    }

    public function delete($slug)
    {

        $tag = Tag::where('slug',$slug)->first();

        if (!$tag) {
            return response()->json(['error' => 'Tag not found'], 404);
        }

        $tag->delete();

        return response()->json(['message' => 'Tag deleted']);
    }

    public function update(Request $request, $slug)
    {
        $tag = Tag::where('slug',$slug)->first();

        if (!$tag) {
            return response()->json(['error' => 'Tag not found'], 404);
        }

        $request->validate([
            'name' => [
                'required','string','min:2','max:50',
                Rule::unique('tags')->ignore($tag->id),
            ],
        ]);

        if ($request->name != $tag->name){
            $tag->slug = null;
            $tag->update([
                'name' => $request->name,
            ]);
        }else {
            $tag->save();
        }

        return $tag;
    }

    public function show($slug)
    {
        $tag = Tag::where('slug',$slug)->first();

        if (!$tag) {
            return response()->json(['error' => 'Tag not found'], 404);
        }

        return $tag;
    }
}
