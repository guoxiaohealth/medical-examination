<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;


    public function respondWithData($v)
    {
        return response()->json($v);
    }

    public function respondWithSuccess()
    {
        return response()->json('success');
    }

    public function respondWithError($error, $status = 400)
    {
        return response()->json(['error' => $error], $status);
    }
}
