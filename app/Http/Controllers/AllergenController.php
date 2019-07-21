<?php

namespace aleafoodapi\Http\Controllers;

use aleafoodapi\Allergen;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AllergenController extends Controller
{
    public function index()
    {
        $allergens = Allergen::all();
        return $allergens;
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|min:2|max:30|unique:allergens',
        ]);

        $allergen = Allergen::create([
            'name' => $request->name,
        ]);

        return $allergen;
    }

    public function delete($id)
    {

        $allergen = Allergen::find($id);

        if (!$allergen) {
            return response()->json(['error' => 'Allergen not found'], 404);
        }

        $allergen->delete();

        return response()->json(['message' => 'Allergen deleted']);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => [
                'required','string','min:2','max:30',
                Rule::unique('allergens')->ignore($id),
            ],
        ]);

        $allergen = Allergen::find($id);

        if (!$allergen) {
            return response()->json(['error' => 'Allergen not found'], 404);
        }

        $allergen->name = $request->name;
        $allergen->save();

        return $allergen;
    }

    public function show($id)
    {
        $allergen = Allergen::find($id);

        if (!$allergen) {
            return response()->json(['error' => 'Allergen not found'], 404);
        }

        return $allergen;
    }
}
