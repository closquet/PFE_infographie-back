<?php

namespace aleafoodapi\Http\Controllers;

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
    public function showloggedInUser (Request $request)
    {
        return $request->user();
    }

    /**
     * add or change the avatar of logged in user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update_avatar(Request $request)
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

        return response()->json([
            "message" => "The avatar has been updated",
            "user" => $user,
        ])->setStatusCode(200);

    }

    /**
     * delete the avatar of logged in user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete_avatar(Request $request)
    {
        $user = Auth::user();

        Storage::delete($user->avatar);
        $user->avatar = null;
        $user->save();

        return response()->json([
            "message" => "The avatar has been deleted",
            "user" => $user,
        ])->setStatusCode(200);

    }
}
