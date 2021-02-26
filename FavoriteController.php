<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    public function toggle(Request $request)
    {
        /** @var Favorite $favorite */
        $favorite = Favorite::where([
            'user_id' => Auth::user()->id,
            'product_id' => $request->post('product_id'),
            'product_type' => $request->post('product_type'),
        ])->first();

        if ($favorite === null) {
            $favorite = new Favorite();
            $favorite->user_id = Auth::user()->id;
            $favorite->product_id = $request->post('product_id');
            $favorite->product_type = $request->post('product_type');
            $favorite->save();

            return '{"status":"ok","action":"added"}';
        } else {
            $favorite->delete();
            return '{"status":"ok","action":"removed"}';
        }
    }

    /**
     * Get all favorite posts by user
     *
     * @return Response
     */
    public function myFavorites()
    {
        /**$myFavorites = Auth::user()->favorites;*/
        $myFavoritesElectricity = Favorite::where(['user_id' => Auth::user()->id, 'product_type' => Favorite::TYPE_ELECTRICITY])
            ->select(['favorites.*', 'tovaru_electrica.url', 'tovaru_electrica.photo', 'tovaru_electrica.price', 'tovaru_electrica.name_description'])
            ->join('tovaru_electrica', 'tovaru_electrica.id', '=', 'favorites.product_id')
            ->get();
        $myFavoritesPlumbing = Favorite::where(['user_id' => Auth::user()->id, 'product_type' => Favorite::TYPE_PLUMBING])
            ->select(['favorites.*', 'tovaru_santechnica.url', 'tovaru_santechnica.photo', 'tovaru_santechnica.price', 'tovaru_santechnica.name_description'])
            ->join('tovaru_santechnica', 'tovaru_santechnica.id', '=', 'favorites.product_id')
            ->get();

        return view(
            'my_favorites',
            ['myFavoritesElectricity' => $myFavoritesElectricity, 'myFavoritesPlumbing' => $myFavoritesPlumbing]
        );
    }

    public function destroy($id)
    {
        $favorite = Favorite::find($id);
        if ($favorite === null) {
            return response()->json([
                'status' => 'error'
            ]);
        } else {
            $favorite->delete();
            return response()->json([
                'status' => 'ok'
            ]);
        }

    }
}
