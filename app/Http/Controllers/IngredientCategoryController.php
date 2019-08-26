<?php

namespace Aleafoodapi\Http\Controllers;

use Aleafoodapi\IngredientCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Intervention\Image\Facades\Image;

class IngredientCategoryController extends Controller
{
    public function index()
    {
        $categories = IngredientCategory::all();
        return $categories;
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|min:2|max:50|unique:ingredient_categories',
        ]);

        $category = new IngredientCategory;
        $category->name = $request->name;
        $category->save();

        return $category;
    }

    public function delete($slug)
    {

        $category = IngredientCategory::where('slug',$slug)->first();

        if (!$category) {
            return response()->json(['error' => 'Ingredient category not found'], 404);
        }

        if ($category->thumbnail) {
            Storage::delete($category->thumbnail);
            Storage::delete(str_replace("thumbnail", "banner", $category->thumbnail));
            Storage::delete(str_replace("thumbnail", "largeBanner", $category->thumbnail));
            $category->thumbnail = null;
        }

        $category->delete();

        return response()->json(['message' => 'Ingredient category deleted']);
    }

    public function update(Request $request, $slug)
    {
        $category = IngredientCategory::where('slug',$slug)->first();

        if (!$category) {
            return response()->json(['error' => 'Ingredient category not found'], 404);
        }

        $request->validate([
            'name' => [
                'required','string','min:2','max:50',
                Rule::unique('ingredient_categories')->ignore($category->id),
            ],
        ]);

        if ($request->name != $category->name){
            $category->slug = null;
            $category->update([
                'name' => $request->name,
            ]);
        }else {
            $category->save();
        }

        return $category;
    }

    public function show($slug)
    {
        $category = IngredientCategory::with('subCategories')->where('slug',$slug)->first();

        if (!$category) {
            return response()->json(['error' => 'Ingredient category not found'], 404);
        }

        return $category;
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

        $category = IngredientCategory::where('slug',$slug)->first();

        if (!$category) {
            return response()->json(['error' => 'Ingredient category not found'], 404);
        }

        if ($category->thumbnail) {
            Storage::delete($category->thumbnail);
            Storage::delete(str_replace("thumbnail", "banner", $category->thumbnail));
            Storage::delete(str_replace("thumbnail", "largeBanner", $category->thumbnail));
            $category->thumbnail = null;
        }

        $thumbnailName = $category->slug.'_thumbnail'.time().'.'.request()->thumbnail->getClientOriginalExtension();
        $bannerName = $category->slug.'_banner'.time().'.'.request()->thumbnail->getClientOriginalExtension();
        $bannerLargeName = $category->slug.'_largeBanner'.time().'.'.request()->thumbnail->getClientOriginalExtension();

        $thumbnailPath = $request->thumbnail->storeAs('thumbnails',$thumbnailName);
        $bannerPath = $request->thumbnail->storeAs('banners',$bannerName);
        $bannerLargePath = $request->thumbnail->storeAs('largeBanners',$bannerLargeName);

        $thumbnail = Image::make(Storage::get($thumbnailPath));
        $banner = Image::make(Storage::get($bannerPath));
        $bannerLarge = Image::make(Storage::get($bannerLargePath));

        $thumbnail->fit(335, 80, function ($constraint) {
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

        $category->thumbnail = $thumbnailPath;
        $category->save();

        return response()->json($category)->setStatusCode(200);
    }
}
