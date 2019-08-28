<?php

namespace Aleafoodapi\Http\Controllers;

use Aleafoodapi\Recipe;
use Aleafoodapi\Step;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Intervention\Image\Facades\Image;

class RecipeController extends Controller
{
    public function index()
    {
        $recipes = Recipe::orderBy('created_at', 'desc')->get();
        return $recipes;
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|min:2|max:50|unique:recipes',
            'description' => 'nullable|string|max:500',
            'preparation_time' => 'required|integer|max:5000',
            'cooking_time' => 'required|integer|max:5000',
            'persons' => 'required|integer|max:100',

            'ingredients' => 'required|array',
            'ingredients.*' => 'required|array',

            'ingredients.*.ingredient_id' => 'required|integer|exists:ingredients,id',
            'ingredients.*.detail' => 'nullable|string|max:50',
            'ingredients.*.amount' => 'required|numeric|max:50000',
            'ingredients.*.measure' => 'required|string|max:50',

            'tags' => 'present|array',
            'tags.*' => 'integer|exists:tags,id',
            'steps' => 'required|array',
            'steps.*' => 'required|string',
        ]);

        $recipe = new Recipe;
        $user = Auth::user();

        $recipe->name = $request->name;
        $recipe->description = $request->description;
        $recipe->preparation_time = $request->preparation_time;
        $recipe->cooking_time = $request->cooking_time;
        $recipe->persons = $request->persons;
        $recipe->user_id = $user->id;
        $recipe->save();

        foreach ($request->ingredients as $key => $value){
            $recipe->ingredients()->attach($value['ingredient_id'], [
                'detail'=> $value['detail'],
                'amount'=> $value['amount'],
                'measure'=> $value['measure'],
            ]);
        }

        foreach ($request->steps as $key => $value){
            $recipe->steps()->create([
                'step_number'=> $key + 1,
                'content'=> $value,
            ]);
        }

        $recipe->tags()->sync($request->tags);
        $recipe = $recipe->fresh();

        $recipe->load('ingredients', 'ingredients.allergens:name,slug', 'user:name,slug,avatar', 'steps', 'tags');

        return response()->json($recipe);
    }

    public function delete($slug)
    {

        $recipe = Recipe::where('slug',$slug)->first();

        if (!$recipe) {
            return response()->json(['error' => 'Recipe not found'], 404);
        }

        if ($recipe->thumbnail) {
            Storage::delete($recipe->thumbnail);
            Storage::delete(str_replace("thumbnail", "banner", $recipe->thumbnail));
            Storage::delete(str_replace("thumbnail", "largeBanner", $recipe->thumbnail));
            $recipe->thumbnail = null;
        }

        $recipe->delete();

        return response()->json(['message' => 'Recipe deleted']);
    }

    public function update(Request $request, $slug)
    {
        $recipe = Recipe::where('slug',$slug)->first();

        if (!$recipe) {
            return response()->json(['error' => 'Recipe not found'], 404);
        }

        $request->validate([
            'name' => [
                'required','string','min:2','max:50',
                Rule::unique('recipes')->ignore($recipe->id),
            ],
            'description' => 'nullable|string|max:500',
            'preparation_time' => 'required|integer|max:5000',
            'cooking_time' => 'required|integer|max:5000',
            'persons' => 'required|integer|max:100',

            'ingredients' => 'required|array',
            'ingredients.*' => 'required|array',

            'ingredients.*.ingredient_id' => 'required|integer|exists:ingredients,id',
            'ingredients.*.detail' => 'nullable|string|max:50',
            'ingredients.*.amount' => 'required|numeric|max:50000',
            'ingredients.*.measure' => 'required|string|max:50',

            'tags' => 'present|array',
            'tags.*' => 'integer|exists:tags,id',
            'steps' => 'required|array',
            'steps.*' => 'required|string',
        ]);

        $recipe->steps()->delete();
        foreach ($request->steps as $key => $value){
            $recipe->steps()->create([
                'step_number'=> $key + 1,
                'content'=> $value,
            ]);
        }

        $recipe->description = $request->description;
        $recipe->preparation_time = $request->preparation_time;
        $recipe->cooking_time = $request->cooking_time;
        $recipe->persons = $request->persons;

        if ($request->name != $recipe->name){
            $recipe->slug = null;
            $recipe->update([
                'name' => $request->name,
            ]);
        }else {
            $recipe->save();
        }

        $recipe->ingredients()->detach();

        foreach ($request->ingredients as $key => $value){
            $recipe->ingredients()->attach($value['ingredient_id'], [
                'detail'=> $value['detail'],
                'amount'=> $value['amount'],
                'measure'=> $value['measure'],
            ]);
        }

        $recipe->tags()->sync($request->tags);

        $recipe = $recipe->fresh();
        $recipe->load('ingredients', 'ingredients.allergens:name,slug', 'user:name,slug,avatar', 'steps', 'tags');

        return $recipe;
    }

    public function show($slug)
    {
        $recipe = Recipe::where('slug',$slug)->first();
        $recipe->load('ingredients:ingredient_id,name,detail,amount,measure', 'ingredients.allergens:name,slug', 'user:name,slug,avatar', 'steps', 'tags');

        if (!$recipe) {
            return response()->json(['error' => 'Recipe not found'], 404);
        }

        return response()->json($recipe);
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
            'thumbnail' => 'required|image|mimes:jpeg,png,jpg,gifg|max:2048',
        ]);

        $recipe = Recipe::with(['user'])->where('slug',$slug)->first();

        if (!$recipe) {
            return response()->json(['error' => 'Recipe not found'], 404);
        }

        if ($recipe->thumbnail) {
            Storage::delete(str_replace("thumbnail", "banner", $recipe->thumbnail));
            Storage::delete(str_replace("thumbnail", "largeBanner", $recipe->thumbnail));
            Storage::delete($recipe->thumbnail);
            $recipe->thumbnail = null;
        }
        $thumbnailName = $recipe->slug.'_thumbnail'.time().'.'.request()->thumbnail->getClientOriginalExtension();
        $bannerName = $recipe->slug.'_banner'.time().'.'.request()->thumbnail->getClientOriginalExtension();
        $bannerLargeName = $recipe->slug.'_largeBanner'.time().'.'.request()->thumbnail->getClientOriginalExtension();

        $thumbnailPath = $request->thumbnail->storeAs('thumbnails',$thumbnailName);
        $bannerPath = $request->thumbnail->storeAs('banners',$bannerName);
        $bannerLargePath = $request->thumbnail->storeAs('largeBanners',$bannerLargeName);

        $thumbnail = Image::make(Storage::get($thumbnailPath));
        $banner = Image::make(Storage::get($bannerPath));
        $bannerLarge = Image::make(Storage::get($bannerLargePath));

        $thumbnail->fit(400, 400, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        $banner->fit(800, 400, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        $bannerLarge->fit(1600, 800, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        $thumbnail->save('storage/' . $thumbnailPath);
        $banner->save('storage/' . $bannerPath);
        $bannerLarge->save('storage/' . $bannerLargePath);

        $recipe->thumbnail = $thumbnailPath;
        $recipe->save();

        $recipe->load('ingredients', 'ingredients.allergens:name,slug', 'user:name,slug,avatar', 'steps', 'tags');

        return response()->json($recipe);
    }


    public function deleteThumbnail($slug)
    {
        $recipe = Recipe::where('slug',$slug)->first();

        if (!$recipe) {
            return response()->json(['error' => 'Recipe not found'], 404);
        }

        Storage::delete(str_replace("thumbnail", "banner", $recipe->thumbnail));
        Storage::delete(str_replace("thumbnail", "largeBanner", $recipe->thumbnail));
        Storage::delete($recipe->thumbnail);
        $recipe->thumbnail = null;
        $recipe->save();

        $recipe->load('ingredients', 'ingredients.allergens:name,slug', 'user:name,slug,avatar', 'steps', 'tags');

        return response()->json($recipe)->setStatusCode(200);
    }
}
