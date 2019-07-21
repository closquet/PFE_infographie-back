<?php

namespace aleafoodapi\Http\Controllers;

use Illuminate\Http\Request;
use aleafoodapi\Ingredient;
use Illuminate\Validation\Rule;

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
            'sub_cat_id' => 'required|integer|exists:ingredient_sub_cats,id',
        ]);

        $ingredient = new Ingredient;
        $ingredient->name = $request->name;
        $ingredient->sub_cat_id = $request->sub_cat_id;
        $ingredient->save();
        $ingredient->allergens()->sync($request->allergens);
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
}
