<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\JsonResponse;

class Controller extends BaseController
{
    protected function buildFailedValidationResponse(\Illuminate\Http\Request $request, array $errors)
    {
        return new JsonResponse([
            "code"=> 422,
            "message" => current(current($errors)),
            // "errors" => $errors
        ], 422);
    }
}
