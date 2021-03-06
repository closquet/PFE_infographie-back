<?php

namespace Aleafoodapi\Http\Controllers;

use Aleafoodapi\Allergen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Intervention\Image\Facades\Image;

class AllergenController extends Controller
{
    public function index()
    {
        $allergens = Allergen::orderBy('created_at', 'desc')->get();
        return $allergens;
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|min:2|max:50|unique:allergens',
        ]);

        $allergen = Allergen::create([
            'name' => $request->name,
        ]);

        return $allergen;
    }

    public function delete($slug)
    {

        $allergen = Allergen::where('slug',$slug)->first();

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

    public function update(Request $request, $slug)
    {
        $allergen = Allergen::where('slug',$slug)->first();

        if (!$allergen) {
            return response()->json(['error' => 'Allergen not found'], 404);
        }

        $request->validate([
            'name' => [
                'required','string','min:2','max:50',
                Rule::unique('allergens')->ignore($allergen->id),
            ],
        ]);

        if ($request->name != $allergen->name){
            $allergen->slug = null;
            $allergen->update([
                'name' => $request->name,
            ]);
        }else {
            $allergen->save();
        }

        return $allergen;
    }

    public function show($slug)
    {
        $allergen = Allergen::where('slug',$slug)->first();

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
    public function updateThumbnail(Request $request, $slug)
    {
        $request->validate([
            'thumbnail' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $allergen = Allergen::where('slug',$slug)->first();

        if (!$allergen) {
            return response()->json(['error' => 'Allergen not found'], 404);
        }

        if ($allergen->thumbnail) {
            Storage::delete($allergen->thumbnail);
            $allergen->thumbnail = null;
        }

        $thumbnailName = $allergen->slug.'_thumbnail'.time().'.'.request()->thumbnail->getClientOriginalExtension();

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
