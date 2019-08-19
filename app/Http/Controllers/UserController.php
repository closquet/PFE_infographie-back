<?php

namespace Aleafoodapi\Http\Controllers;

use Aleafoodapi\Recipe;
use Aleafoodapi\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
            'allergens:name,id',
            'disliked_ingredients:name,id',
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
            'name' => 'nullable|string|max:255',
            'old_password' => 'nullable|string|min:8',
            'new_password' => 'nullable|string|min:8',
            'description' => 'nullable|string|max:255',
            'allergens' => 'present|array',
            'allergens.*' => 'integer|exists:allergens,id',
            'disliked_ingredients' => 'present|array',
            'disliked_ingredients.*' => 'integer|exists:ingredients,id',
        ]);

        $user = Auth::user();
        $user->load([
            'allergens:name,id',
            'disliked_ingredients:name,id',
            'liked_recipes:slug',
        ]);

        if ( ($request->new_password && !$request->old_password) || (!$request->new_password && $request->old_password) ) {
            return response()->json(['error' => __('auth.failed_newpassword_oldpassword')], 401);
        }

        if ($request->new_password && $request->old_password && !Hash::check($request->old_password, $user->password)) {
            return response()->json(['error' => __('auth.failed_password')], 401);
        }

        if ($request->old_password && $request->new_password && !Hash::check($request->new_password, $user->password)) {
            $user->password = Hash::make($request->new_password);
        }

        if ($request->allergens != $user->allergens) {
            $user->allergens()->sync($request->allergens);
        }

        if ($request->disliked_ingredients != $user->disliked_ingredients) {
            $user->disliked_ingredients()->sync($request->disliked_ingredients);
        }

        if ($request->description != $user->description) {
            $user->description = $request->description;
        }

        if ($request->name && $request->name != $user->name){
            $user->slug = null;
            $user->update([
                'name' => $request->name,
            ]);
        }else {
            $user->save();
        }

        $user = $user->fresh();
        $user->load([
            'allergens:name,id',
            'disliked_ingredients:name,id',
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
            'allergens:name,id',
            'disliked_ingredients:name,id',
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
            'allergens:name,id',
            'disliked_ingredients:name,id',
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
            'allergens:name,id',
            'disliked_ingredients:name,id',
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
            'allergens:name,id',
            'disliked_ingredients:name,id',
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
