<?php

namespace Aleafoodapi\Http\Controllers;

use Aleafoodapi\Recipe;
use Aleafoodapi\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class UserController extends Controller
{
    /**
     * get data of logged in user
     *
     * @param Request $request
     * @return mixed
     */
    public function showLoggedInUser (Request $request)
    {
        $user = User::with([
            'allergens:name,slug',
            'disliked_ingredients:name,slug',
            'liked_recipes:slug',
        ])->findOrFail($request->user()->id);
        return $user;
    }

    /**
     * edit the data of logged in user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function editLoggedInUser (Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'allergens' => 'present|array',
            'allergens.*' => 'integer|exists:allergens,id',
            'disliked_ingredients' => 'present|array',
            'disliked_ingredients.*' => 'integer|exists:ingredients,id',
        ]);

        $user = Auth::user();

            $user->allergens()->sync($request->allergens);
            $user->disliked_ingredients()->sync($request->disliked_ingredients);
            $user->description = $request->description;

        if ($request->name != $user->name){
            $user->slug = null;
            $user->update([
                'name' => $request->name,
            ]);
        }else {
            $user->save();
        }

        $user = $user->fresh();
        $user->load([
            'allergens:name,slug',
            'disliked_ingredients:name,slug',
            'liked_recipes:slug',
        ]);

        return response()->json($user)->setStatusCode(200);
    }

    /**
     * add or change the avatar of logged in user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $user = Auth::user();

        if ($user->avatar) {
            $this->delete_avatar($request);
        }

        $avatarName = $user->slug.'_avatar'.time().'.'.request()->avatar->getClientOriginalExtension();

        $path = $request->avatar->storeAs('avatars',$avatarName);

        $resizedImg = Image::make(Storage::get($path));

        $resizedImg->fit(400, 400, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        $resizedImg->save('storage/' . $path);

        $user->avatar = $path;
        $user->save();
        $user->load([
            'allergens:name,slug',
            'disliked_ingredients:name,slug',
            'liked_recipes:slug',
        ]);

        return response()->json($user)->setStatusCode(200);
    }

    /**
     * delete the avatar of logged in user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteAvatar(Request $request)
    {
        $user = Auth::user();

        Storage::delete($user->avatar);
        $user->avatar = null;
        $user->save();
        $user->load([
            'allergens:name,slug',
            'disliked_ingredients:name,slug',
            'liked_recipes:slug',
        ]);

        return response()->json($user)->setStatusCode(200);
    }

    /**
     * get all users
     *
     * @return User[]|\Illuminate\Database\Eloquent\Collection
     */
    public function index()
    {
        return User::all();
    }

    /**
     * get user's data by slug
     *
     * @param $slug
     * @return \Illuminate\Http\JsonResponse
     */
    public function showBySlug($slug)
    {
        $user = User::with([
            'allergens:name,slug',
            'disliked_ingredients:name,slug',
            'liked_recipes:slug',
        ])->where('slug', $slug)->first();

        if ($user){
            return response()->json([
                "name"=> $user->name,
                "slug"=> $user->slug,
                "avatar"=> $user->avatar,
                "description"=> $user->description,
                "email"=> $user->email,
            ])->setStatusCode(200);
        }

        return response()->json([
            "error" => "User not found",
        ])->setStatusCode(404);
    }


    public function likeRecipe($slug)
    {
        $recipe = Recipe::where('slug',$slug)->first();

        if (!$recipe) {
            return response()->json(['error' => 'Recipe not found'], 404);
        }

        $user = Auth::user();


        if (!$user->liked_recipes->where('slug',$slug)->first()){
            $user->liked_recipes()->attach($recipe->id);
            $user = $user->fresh();
        }

        $user->load([
            'allergens:name,slug',
            'disliked_ingredients:name,slug',
            'liked_recipes:slug',
        ]);

        return $user;
    }


    public function removeLikeRecipe($slug)
    {
        $recipe = Recipe::where('slug',$slug)->first();

        if (!$recipe) {
            return response()->json(['error' => 'Recipe not found'], 404);
        }

        $user = Auth::user();

        $user->liked_recipes()->detach($recipe->id);

        $user = $user->fresh();

        $user->load([
            'allergens:name,slug',
            'disliked_ingredients:name,slug',
            'liked_recipes:slug',
        ]);

        return $user;
    }
}
