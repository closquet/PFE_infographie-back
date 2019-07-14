<?php

namespace aleafoodapi\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function showloggedInUser (Request $request) {
        return $request->user();
    }
}
