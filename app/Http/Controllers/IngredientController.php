<?php

namespace Aleafoodapi\Http\Controllers;

use Illuminate\Http\Request;
use Aleafoodapi\Ingredient;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Intervention\Image\Facades\Image;

class IngredientController extends Controller
{
    public function index()
    {
        $ingredients = Ingredient::with([
            'allergens:name,slug,id',
            'seasons:name,slug,id',
        ])->orderBy('created_at', 'desc')->get();
        return $ingredients;
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|min:2|max:50|unique:ingredients',
            'allergens' => 'present|array',
            'allergens.*' => 'integer|exists:allergens,id',
            'seasons' => 'present|array',
            'seasons.*' => 'integer|exists:seasons,id',
            'sub_cat_id' => 'required|integer|exists:ingredient_sub_cats,id',
        ]);

        $ingredient = new Ingredient;
        $ingredient->name = $request->name;
        $ingredient->sub_cat_id = $request->sub_cat_id;
        $ingredient->save();
        $ingredient->allergens()->sync($request->allergens);
        $ingredient->seasons()->sync($request->seasons);
        $ingredient = $ingredient->fresh();

        $ingredient->load([
            'allergens:name,slug,id',
            'seasons:name,slug,id',
        ]);

        return $ingredient;
    }

    public function delete($slug)
    {

        $ingredient = Ingredient::where('slug',$slug)->first();

        if (!$ingredient) {
            return response()->json(['error' => 'Ingredient not found'], 404);
        }

        if ($ingredient->thumbnail) {
            Storage::delete($ingredient->thumbnail);
            $ingredient->thumbnail = null;
        }

        $ingredient->delete();

        return response()->json(['message' => 'Ingredient deleted']);
    }

    public function update(Request $request, $slug)
    {
        $ingredient = Ingredient::where('slug',$slug)->first();

        if (!$ingredient) {
            return response()->json(['error' => 'Ingredient not found'], 404);
        }

        $request->validate([
            'name' => [
                'required','string','min:2','max:50',
                Rule::unique('ingredients')->ignore($ingredient->id),
            ],
            'allergens' => 'present|array',
            'allergens.*' => 'integer|exists:allergens,id',
            'seasons' => 'present|array',
            'seasons.*' => 'integer|exists:seasons,id',
            'sub_cat_id' => 'required|integer|exists:ingredient_sub_cats,id',
        ]);

        $ingredient->sub_cat_id = $request->sub_cat_id;
        $ingredient->save();
        $ingredient->allergens()->sync($request->allergens);
        $ingredient->seasons()->sync($request->seasons);

        if ($request->name != $ingredient->name){
            $ingredient->slug = null;
            $ingredient->update([
                'name' => $request->name,
            ]);
        }else {
            $ingredient->save();
        }

        $ingredient = $ingredient->fresh();

        $ingredient->load([
            'allergens:name,slug,id',
            'seasons:name,slug,id',
        ]);

        return $ingredient;
    }

    public function show($slug)
    {
        $ingredient = Ingredient::where('slug',$slug)->first();

        if (!$ingredient) {
            return response()->json(['error' => 'Ingredient not found'], 404);
        }

        $ingredient->load([
            'allergens:name,slug,id',
            'seasons:name,slug,id',
        ]);

        return $ingredient;
    }


    /**
     * add or change the thumbnail
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateThumbnail(Request $request, $slug)
    {
        $request->validate([
            'thumbnail' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $ingredient = Ingredient::where('slug',$slug)->first();

        if (!$ingredient) {
            return response()->json(['error' => 'Ingredient not found'], 404);
        }

        if ($ingredient->thumbnail) {
            Storage::delete($ingredient->thumbnail);
            $ingredient->thumbnail = null;
        }

        $thumbnailName = $ingredient->slug.'_thumbnail'.time().'.'.request()->thumbnail->getClientOriginalExtension();

        $thumbnailsPath = $request->thumbnail->storeAs('thumbnails',$thumbnailName);

        $thumbnail = Image::make(Storage::get($thumbnailsPath));

        $thumbnail->fit(400, 400, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        $thumbnail->save('storage/' . $thumbnailsPath);

        $ingredient->thumbnail = $thumbnailsPath;
        $ingredient->save();

        return response()->json($ingredient)->setStatusCode(200);
    }


    public function deleteThumbnail($slug)
    {
        $ingredient = Ingredient::where('slug',$slug)->first();

        if (!$ingredient) {
            return response()->json(['error' => 'Ingredient not found'], 404);
        }

        Storage::delete($ingredient->thumbnail);
        $ingredient->thumbnail = null;
        $ingredient->save();

        $ingredient->load([
            'allergens:name,slug,id',
            'seasons:name,slug,id',
        ]);

        return response()->json($ingredient)->setStatusCode(200);
    }
}
