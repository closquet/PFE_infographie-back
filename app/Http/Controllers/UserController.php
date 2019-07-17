<?php

namespace aleafoodapi\Http\Controllers;

use aleafoodapi\User;
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
        return $request->user();
    }

    /**
     * edit the data of logged in user
     *
     * @param Request $request
     */
    public function editLoggedInUser (Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();


        if ($request->name != $user->name){
            $user->slug = null;
            $user->update([
                'name' => $request->name,
                'description' => $request->description
            ]);
        }else {
            $user->description = $request->description;
            $user->save();
        }



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
        $user = User::where('slug', $slug)->first();

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
}
