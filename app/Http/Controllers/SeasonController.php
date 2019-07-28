<?php

namespace aleafoodapi\Http\Controllers;

use aleafoodapi\Season;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SeasonController extends Controller
{
    public function index()
    {
        $seasons = Season::all();
        return $seasons;
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|min:2|max:30|unique:seasons',
        ]);

        $season = new Season;
        $season->name = $request->name;
        $season->save();

        return $season;
    }

    public function delete($slug)
    {

        $season = Season::where('slug',$slug)->first();

        if (!$season) {
            return response()->json(['error' => 'Season not found'], 404);
        }

        $season->delete();

        return response()->json(['message' => 'Season deleted']);
    }

    public function update(Request $request, $slug)
    {
        $season = Season::where('slug',$slug)->first();

        if (!$season) {
            return response()->json(['error' => 'Season not found'], 404);
        }

        $request->validate([
            'name' => [
                'required','string','min:2','max:30',
                Rule::unique('seasons')->ignore($season->id),
            ],
        ]);

        if ($request->name != $season->name){
            $season->slug = null;
            $season->update([
                'name' => $request->name,
            ]);
        }else {
            $season->save();
        }

        return $season;
    }

    public function show($slug)
    {
        $season = Season::where('slug',$slug)->first();

        if (!$season) {
            return response()->json(['error' => 'Season not found'], 404);
        }

        return $season;
    }
}
