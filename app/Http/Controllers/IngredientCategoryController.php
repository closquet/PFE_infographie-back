<?php

namespace aleafoodapi\Http\Controllers;

use aleafoodapi\IngredientCategory;
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
            'name' => 'required|string|min:2|max:30|unique:ingredient_categories',
        ]);

        $category = new IngredientCategory;
        $category->name = $request->name;
        $category->save();

        return $category;
    }

    public function delete($id)
    {

        $category = IngredientCategory::find($id);

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

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => [
                'required','string','min:2','max:30',
                Rule::unique('ingredient_categories')->ignore($id),
            ],
        ]);

        $category = IngredientCategory::find($id);

        if (!$category) {
            return response()->json(['error' => 'Ingredient category not found'], 404);
        }

        $category->name = $request->name;
        $category->save();

        return $category;
    }

    public function show($id)
    {
        $category = IngredientCategory::find($id);

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
    public function updateThumbnail(Request $request, $id)
    {
        $request->validate([
            'thumbnail' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $category = IngredientCategory::find($id);

        if (!$category) {
            return response()->json(['error' => 'Ingredient category not found'], 404);
        }

        if ($category->thumbnail) {
            Storage::delete($category->thumbnail);
            Storage::delete(str_replace("thumbnail", "banner", $category->thumbnail));
            Storage::delete(str_replace("thumbnail", "largeBanner", $category->thumbnail));
            $category->thumbnail = null;
        }

        $thumbnailName = Str::slug($category->name).'_thumbnail'.time().'.'.request()->thumbnail->getClientOriginalExtension();
        $bannerName = Str::slug($category->name).'_banner'.time().'.'.request()->thumbnail->getClientOriginalExtension();
        $bannerLargeName = Str::slug($category->name).'_largeBanner'.time().'.'.request()->thumbnail->getClientOriginalExtension();

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
