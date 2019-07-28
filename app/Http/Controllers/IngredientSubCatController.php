<?php

namespace aleafoodapi\Http\Controllers;

use aleafoodapi\IngredientSubCat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Intervention\Image\Facades\Image;

class IngredientSubCatController extends Controller
{
    public function index()
    {
        $ingredientSubCategories = IngredientSubCat::all();
        return $ingredientSubCategories;
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|min:2|max:30|unique:ingredient_sub_cats',
            'cat_id' => 'required|integer|exists:ingredient_categories,id',
        ]);

        $ingredientSubCategories = new IngredientSubCat;
        $ingredientSubCategories->name = $request->name;
        $ingredientSubCategories->cat_id = $request->cat_id;
        $ingredientSubCategories->save();

        return $ingredientSubCategories;
    }

    public function delete($id)
    {

        $ingredientSubCategories = IngredientSubCat::find($id);

        if (!$ingredientSubCategories) {
            return response()->json(['error' => 'Ingredient sub category not found'], 404);
        }

        if ($ingredientSubCategories->thumbnail) {
            Storage::delete($ingredientSubCategories->thumbnail);
            Storage::delete(str_replace("thumbnail", "banner", $ingredientSubCategories->thumbnail));
            Storage::delete(str_replace("thumbnail", "largeBanner", $ingredientSubCategories->thumbnail));
            $ingredientSubCategories->thumbnail = null;
        }

        $ingredientSubCategories->delete();

        return response()->json(['message' => 'Ingredient sub category deleted']);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => [
                'required','string','min:2','max:30',
                Rule::unique('ingredient_sub_cats')->ignore($id),
            ],
            'cat_id' => 'required|integer|exists:ingredient_categories,id',
        ]);

        $ingredientSubCategory = IngredientSubCat::find($id);

        if (!$ingredientSubCategory) {
            return response()->json(['error' => 'Ingredient sub category not found'], 404);
        }

        $ingredientSubCategory->name = $request->name;
        $ingredientSubCategory->cat_id = $request->cat_id;
        $ingredientSubCategory->save();

        return $ingredientSubCategory;
    }

    public function show($id)
    {
        $ingredientSubCategory = IngredientSubCat::find($id);

        if (!$ingredientSubCategory) {
            return response()->json(['error' => 'Ingredient sub category not found'], 404);
        }

        return $ingredientSubCategory;
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

        $ingredientSubCategories = IngredientSubCat::find($id);

        if (!$ingredientSubCategories) {
            return response()->json(['error' => 'Ingredient sub category not found'], 404);
        }

        if ($ingredientSubCategories->thumbnail) {
            Storage::delete($ingredientSubCategories->thumbnail);
            Storage::delete(str_replace("thumbnail", "banner", $ingredientSubCategories->thumbnail));
            Storage::delete(str_replace("thumbnail", "largeBanner", $ingredientSubCategories->thumbnail));
            $ingredientSubCategories->thumbnail = null;
        }
        $thumbnailName = Str::slug($ingredientSubCategories->name).'_thumbnail'.time().'.'.request()->thumbnail->getClientOriginalExtension();
        $bannerName = Str::slug($ingredientSubCategories->name).'_banner'.time().'.'.request()->thumbnail->getClientOriginalExtension();
        $bannerLargeName = Str::slug($ingredientSubCategories->name).'_largeBanner'.time().'.'.request()->thumbnail->getClientOriginalExtension();

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

        $ingredientSubCategories->thumbnail = $thumbnailPath;
        $ingredientSubCategories->save();

        return response()->json()->setStatusCode(200);
    }
}
