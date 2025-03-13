<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Province;

class ProvinceController extends Controller
{
    // show all provinces
    public function indexIran()
    {
        $provinces = Province::all();

        return response()->json([
            "is_success" => true,
            "status_code" => 200,
            "data" => $provinces,
            "message" => "لیست محصولات مرتبط با درخواست شما",
        ]);
    }
}
