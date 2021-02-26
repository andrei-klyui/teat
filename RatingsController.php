<?php

namespace App\Http\Controllers;

use App\Ratings;
use Illuminate\Http\Request;
use DB;

class RatingsController extends Controller
{
    public function rating(Request $request)
    {
        $ratings = new Ratings();
        $ratings->product_id = $request->product_id;
        $ratings->rating = $request->rating;
        $ratings->ip = $request->ip();
        $ratings->product_type = $request->product_type;
        $ratings->save();
        $message = 'Rated successfully';
        return back()->with($message);
    }
}
