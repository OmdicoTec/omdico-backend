<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UtilsController extends Controller
{
    /**
     * Slug generator for input requests
     * @var Request
     * @access public
     */
    public function slugify(Request $request)
    {
        $slug = $request->input("slug");
        return response()->json([
            "slug"=> Str::slug($slug, "_"),
            "is_success" => true,
            "status_code" => 200,
        ]);
    }

}
