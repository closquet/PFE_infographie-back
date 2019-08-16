<?php

namespace Aleafoodapi\Http\Controllers\Auth;

use Aleafoodapi\Http\Controllers\Controller;
use Aleafoodapi\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            $response = json_decode((string) $response->getBody(), true);

            $user = User::with([
                'allergens:name,slug,id',
                'disliked_ingredients:name,slug,id',
                'liked_recipes:slug,id',
                ])->where('email', $request->email)->firstOrFail();

            $user['tokens'] = $response;

            return $user;
        } catch (BadResponseException $error) {
            if ($error->getCode() === 400) {
                return response()->json(['error' => __('auth.failed')], $error->getCode());
            } else if ($error->getCode() === 401) {
                return response()->json(['error' => __('auth.failed')], $error->getCode());
            }
            return response()->json(['error' => 'Something went wrong on the server.'], $error->getCode());
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
        if ( env('RECAPTCHA_ENABLED') && !$this->checkRecaptcha($request->token) ) {
            return response()->json(['error' => __('validation.custom.reCaptha')], 401);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        User::create([
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
        return response()->json(['message' => 'Logged out successfully'], 200);
    }

    /**
     * Refresh token with proxy to /oauth/token
     *
     * @param Request $request ($request->refresh_token)
     * @return \Illuminate\Http\JsonResponse|mixed (new password token)
     */
    public function refresh(Request $request)
    {
        $http = new Client;

        try {
            $response = $http->post(env('APP_URL') . '/oauth/token', [
                'form_params' => [
                    'grant_type' => 'refresh_token',
                    'client_id' => env('PASSWORD_CLIENT_ID'),
                    'client_secret' => env('PASSWORD_CLIENT_SECRET'),
                    'refresh_token' => $request->refresh_token,
                    'scope' => '',
                ]
            ]);
            return json_decode((string) $response->getBody(), true);
        } catch (BadResponseException $error) {
            if ($error->getCode() === 400) {
                return response()->json('Invalid Request. Please enter an email and a password.', $error->getCode());
            } else if ($error->getCode() === 401) {
                return response()->json(__('auth.failed'), $error->getCode());
            }
        }
    }


    public function checkRecaptcha($token) {
        $http = new Client;
        try {
            $response = $http->post('https://www.google.com/recaptcha/api/siteverify', [
                'form_params' => [
                    'secret' => env('RECAPTCHA_SECRET_KEY'),
                    'response' => $token,
                ]
            ]);

            return json_decode( $response->getBody(), true )['success'];

        } catch (BadResponseException $error) {
            return response()->json(['error' => 'Something went wrong on the server.'], $error->getCode());
        }
    }
}

