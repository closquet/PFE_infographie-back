<?php

namespace aleafoodapi\Http\Controllers;

use Illuminate\Http\Request;
use aleafoodapi\Ingredient;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Intervention\Image\Facades\Image;

class IngredientController extends Controller
{
    public function index()
    {
        $ingredients = Ingredient::all();
        return $ingredients;
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|min:2|max:30|unique:ingredients',
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

        return $ingredient;
    }

    public function delete($id)
    {

        $ingredient = Ingredient::find($id);

        if (!$ingredient) {
            return response()->json(['error' => 'Ingredient not found'], 404);
        }

        $ingredient->delete();

        return response()->json(['message' => 'Ingredient deleted']);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => [
                'required','string','min:2','max:30',
                Rule::unique('ingredients')->ignore($id),
            ],
            'allergens' => 'present|array',
            'allergens.*' => 'integer|exists:allergens,id',
            'seasons' => 'present|array',
            'seasons.*' => 'integer|exists:seasons,id',
            'sub_cat_id' => 'required|integer|exists:ingredient_sub_cats,id',
        ]);

        $ingredient = Ingredient::find($id);

        if (!$ingredient) {
            return response()->json(['error' => 'Ingredient not found'], 404);
        }

        $ingredient->name = $request->name;
        $ingredient->sub_cat_id = $request->sub_cat_id;
        $ingredient->save();
        $ingredient->allergens()->sync($request->allergens);
        $ingredient->seasons()->sync($request->seasons);
        $ingredient = $ingredient->fresh();

        return $ingredient;
    }

    public function show($id)
    {
        $ingredient = Ingredient::find($id);

        if (!$ingredient) {
            return response()->json(['error' => 'Ingredient not found'], 404);
        }

        return $ingredient;
    }


    /**
     * add or change the thumbnail
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateThumbnail(Request $request, $id)
    {
        $request->validate([
            'thumbnail' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $ingredient = Ingredient::find($id);

        if (!$ingredient) {
            return response()->json(['error' => 'Ingredient not found'], 404);
        }

        if ($ingredient->thumbnail) {
            Storage::delete($ingredient->thumbnail);
            $ingredient->thumbnail = null;
        }

        $thumbnailName = Str::slug($ingredient->name).'_thumbnail'.time().'.'.request()->thumbnail->getClientOriginalExtension();

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
}
