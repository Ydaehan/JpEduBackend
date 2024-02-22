<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
* @OA\Info(
*     title="Tamago background", version="0.1", description="Tamago API Documentation",
*     @OA\Contact(
*         email="a01068089833@gmail.com",
*         name="Yoo Daehan"
*     )
* )
*/
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
