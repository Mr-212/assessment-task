<?php

namespace App\Services;

use App\Exceptions\AffiliateCreateException;
use App\Mail\AffiliateCreated;
use App\Models\Affiliate;
use App\Models\Log;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;
use Throwable;

class AffiliateService
{
  

    public function __construct(
        protected ApiService $apiService
    ) {}

    /**
     * Create a new affiliate for the merchant with the given commission rate.
     *
     * @param  Merchant $merchant
     * @param  string $email
     * @param  string $name
     * @param  float $commissionRate
     * @return Affiliate
     */
    public function register(Merchant $merchant, string $email, string $name, float $commissionRate): Affiliate
    {

        // TODO: Complete this method
        \DB::beginTransaction();
        
        $discount_code = $this->apiService->createDiscountCode($merchant)['code'];
        // dd(Affiliate::get());
        Log::create(['key' => 'Discount Code', 'value' => $discount_code]);
        //dd(Log::get());
        $affiliate = new Affiliate([
            'commission_rate' => $commissionRate,
            'merchant_id' => $merchant->id,
            'discount_code' => $discount_code,
        ]);
        // dd($affiliate);

        try{
            $userData = ['name' => $name, 'email' => $email, 'type' => User::TYPE_AFFILIATE];
            $user = User::create($userData);  
            $user->affiliate()->save($affiliate);
            \DB::commit(); 
            Mail::to($email)->send(new AffiliateCreated($affiliate));

        }catch(Throwable $e){
            \DB::rollBack();
            throw new AffiliateCreateException("invalid Email");
            dd($e->getMessage());

        }
        // dd($affiliate);
        return $affiliate;
    }



    /**
     * Find a Affiliate by their email.
     * Hint: You'll need to look up the user first.
     *
     * @param string $email
     * @return Affiliate|null
     */
    public function findAffiliateByEmail(string $email): ?Affiliate
    {
        return Affiliate::whereHas('user',function(Builder $query) use($email){
            $query->where(['email'=>$email,'type' => User::TYPE_AFFILIATE]);
        })->first();
    }

    public function findAffiliateByDiscountCode(string $code): ?Affiliate
    {
        return Affiliate::where('discount_code', $code)->first();
    }
}
