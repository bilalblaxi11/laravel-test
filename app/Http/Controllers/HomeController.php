<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use App\Models\UserProduct;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{

    /**
     * @return View
     */
    public function index(): View
    {
        $allVerifiedUsers = User::whereNotNull('email_verified_at')->whereStatus(1)->count();
        $allVerifiedUsersHasProducts = User::whereNotNull('email_verified_at')
            ->whereStatus(1)->has('products')->count();

        $allActiveProd = Product::whereStatus(1)->count();
        $allActiveProdNotHasUser = Product::whereStatus(1)->doesntHave('users')->count();
        $productAmountHasUser = UserProduct::whereRelation('product', 'status', '=', '1')->sum('qty');
        $productTotalHasUser = UserProduct::whereRelation('product', 'status', '=', '1')
            ->selectRaw('SUM(qty * price) as total')
            ->first('total')->total;

        /*$productTotalWithUser = UserProduct::with('user')
            ->whereRelation('product', 'status', '=', '1')
            ->selectRaw('SUM(qty * price) as total, user_id')->groupBy('user_id')->get();*/

        $productTotalWithUser = User::whereRelation('products', 'status', '=', '1')
            ->select('name')
            ->addSelect([
                'total' => UserProduct::whereColumn('user_id', 'users.id')
                    ->selectRaw('SUM(qty * price) as total')
                    ->limit(1),
            ])->get();


        return view('welcome', [
            'allActiveVerifiedUsers' => $allVerifiedUsers,
            'allActiveVerifiedUsersWithProducts' => $allVerifiedUsersHasProducts,
            'allActiveProducts' => $allActiveProd,
            'allActiveProductsWithoutUser' => $allActiveProdNotHasUser,
            'quantityOfActiveProductsWithUser' => $productAmountHasUser,
            'totalOfActiveProductsWithUser' => $productTotalHasUser,
            'totalActiveProductsPerUser' => $productTotalWithUser->toArray(),
        ]);
    }
}
