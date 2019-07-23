<?php

namespace aleafoodapi\Http\Controllers;

use aleafoodapi\IngredientSubCat;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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

        $ingredientSubCategories = IngredientSubCat::find($id);

        if (!$ingredientSubCategories) {
            return response()->json(['error' => 'Ingredient sub category not found'], 404);
        }

        $ingredientSubCategories->name = $request->name;
        $ingredientSubCategories->cat_id = $request->cat_id;
        $ingredientSubCategories->save();

        return $ingredientSubCategories;
    }

    public function show($id)
    {
        $ingredientSubCategories = IngredientSubCat::find($id);

        if (!$ingredientSubCategories) {
            return response()->json(['error' => 'Ingredient sub category not found'], 404);
        }

        return $ingredientSubCategories;
    }
}
