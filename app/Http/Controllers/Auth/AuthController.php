<?php

namespace aleafoodapi\Http\Controllers\Auth;

use aleafoodapi\Http\Controllers\Controller;
use aleafoodapi\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{
    /**
     * Login with proxy to /oauth/token
     *
     * @param Request $request ($request->username, $request->password)
     * @return \Illuminate\Http\JsonResponse|mixed (password token)
     */
    public function login(Request $request)
    {
        $http = new Client;

        try {
            $response = $http->post(env('APP_URL') . '/oauth/token', [
                'form_params' => [
                    'grant_type' => 'password',
                    'client_id' => env('PASSWORD_CLIENT_ID'),
                    'client_secret' => env('PASSWORD_CLIENT_SECRET'),
                    'username' => $request->email,
                    'password' => $request->password,
                ]
            ]);
            return json_decode((string) $response->getBody(), true);
        } catch (BadResponseException $error) {
            if ($error->getCode() === 400) {
                return response()->json('Invalid Request. Please enter an email and a password.', $error->getCode());
            } else if ($error->getCode() === 401) {
                return response()->json('Your credentials are incorrect. Please try again', $error->getCode());
            }
            return response()->json('Something went wrong on the server.', $error->getCode());
        }
    }

    /**
     * Create a user and execute login methode to return a password token
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user =  User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return $this->login($request);
    }

    public function logout()
    {
        auth()->user()->tokens->each(function ($token, $key) {
            $token->delete();
        });
        return response()->json('Logged out successfully', 200);
    }
}

