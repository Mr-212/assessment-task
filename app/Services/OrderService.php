<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Builder;


class OrderService
{
    
    public function __construct(
        protected AffiliateService $affiliateService,
        protected MerchantService $merchantServices
    ) {
        // $this->affiliateService = app()->make(AffiliateService::class);
        $this->affiliateService = new AffiliateService(new ApiService());

    }

    /**
     * Process an order and log any commissions.
     * This should create a new affiliate if the customer_email is not already associated with one.
     * This method should also ignore duplicates based on order_id.
     *
     * @param  array{order_id: string, subtotal_price: float, merchant_domain: string, discount_code: string, customer_email: string, customer_name: string} $data
     * @return void
     */
    public function processOrder(array $data)
    {
        // TODO: Complete this method
        try{
            /DB::beginTransaction();

            if(!empty($data)){
                if($this->checkDuplicateOrderExists($data['order_id'])) return;
                $email = $data['customer_email'];
                $merchant =  $this->merchantServices->findMerchantByDomainCode($data['merchant_domain']);
                $affiliate = $this->affiliateService->findAffiliateByEmail($data['customer_email']);
                $affiliateByCode = $this->affiliateService->findAffiliateByDiscountCode($data['discount_code']);
                if(empty($affiliate)){
                    $affiliate = $this->affiliateService->register($merchant, $email, $data['customer_name'], 0.1);
                }
                //   dd($affiliate);
            //    dd(round(rand(1, 5) / 10, 1));
                $order = Order::create([
                    // 'order_id' => $data['order_id'], 
                    'external_order_id' => $data['order_id'], 
                    'subtotal' => $data['subtotal_price'], 
                    'commission_owed' => ($data['subtotal_price'] * $affiliate->commission_rate),
                    'merchant_id' => $merchant->id,
                    // 'affiliate_id' => $affiliateByCode->id,
                    'affiliate_id' => $affiliate->id,
                    ]);
                
                // dd($order);
                \DB::commit();
            }

        }catch(Exception $e){
            \DB::rollBack();
            dd($e->getMessage());
            // if($e->getCode())
                // \DB::rollBack();
        }

    }

    public function checkDuplicateOrderExists($order_id) {
        return Order::where('external_order_id', $order_id)->exists();
    }
}
