<?php

namespace aleafoodapi\Http\Controllers;

use aleafoodapi\Allergen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Intervention\Image\Facades\Image;

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

        if ($allergen->thumbnail) {
            Storage::delete($allergen->thumbnail);
            $allergen->thumbnail = null;
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

        $allergen = Allergen::find($id);

        if (!$allergen) {
            return response()->json(['error' => 'Allergen not found'], 404);
        }

        if ($allergen->thumbnail) {
            Storage::delete($allergen->thumbnail);
            $allergen->thumbnail = null;
        }

        $thumbnailName = Str::slug($allergen->name).'_thumbnail'.time().'.'.request()->thumbnail->getClientOriginalExtension();

        $thumbnailsPath = $request->thumbnail->storeAs('thumbnails',$thumbnailName);

        $thumbnail = Image::make(Storage::get($thumbnailsPath));

        $thumbnail->fit(400, 400, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        $thumbnail->save('storage/' . $thumbnailsPath);

        $allergen->thumbnail = $thumbnailsPath;
        $allergen->save();

        return response()->json($allergen)->setStatusCode(200);
    }
}
