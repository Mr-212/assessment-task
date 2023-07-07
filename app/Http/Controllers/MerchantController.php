<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Models\Order;
use App\Services\MerchantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MerchantController extends Controller
{
    public function __construct(
        MerchantService $merchantService
    ) {}

    /**
     * Useful order statistics for the merchant API.
     * 
     * @param Request $request Will include a from and to date
     * @return JsonResponse Should be in the form {count: total number of orders in range, commission_owed: amount of unpaid commissions for orders with an affiliate, revenue: sum order subtotals}
     */
    public function orderStats(Request $request): JsonResponse
    {
        // TODO: Complete this method
        $to = $request->to;
        $from = $request->from;
        $response = ['count' => 0, 'revenue' => 0, 'commissions_owed' => 0];
      
        $query = Order::whereBetween('created_at', [$from, $to]);
        $count = $query->count();
        $subtotal= $query->sum('subtotal');
        $commession_owed = $query->sum('commission_owed');
        $nonAffiliate = $query->whereNull('affiliate_id')->sum('commission_owed');
        // dd($nonAffiliate);

        $response['count'] = $count;
        $response['revenue'] = $subtotal;
        $response['commissions_owed'] = $commession_owed - $nonAffiliate;
        //dd($response);

        return response()->json($response);

        
    }
}
