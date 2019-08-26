<?php

namespace Aleafoodapi\Http\Controllers;

use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function count($slug)
    {
        try{
            $amount = DB::table($slug)->count();
            return $amount;
        }
        catch (BadResponseException $error) {
            return response()->json(['error' => __('validation.exists')], $error->getCode());
        }

    }
}
