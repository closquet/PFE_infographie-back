<?php

namespace aleafoodapi\Http\Controllers;

use aleafoodapi\IngredientCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
}
