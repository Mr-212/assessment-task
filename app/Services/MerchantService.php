<?php

namespace App\Services;

use App\Jobs\PayoutOrderJob;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Builder;


class MerchantService
{
    /**
     * Register a new user and associated merchant.
     * Hint: Use the password field to store the API key.
     * Hint: Be sure to set the correct user type according to the constants in the User model.
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return Merchant
     */
    public function register(array $data): Merchant
    {
      
        // TODO: Complete this method

        \DB::beginTransaction();
        $merchant =  new Merchant([ 
            'display_name' => $data['name'],
            'domain' => $data['domain']
            ]);
        try {

            $user = User::create([
                'email' => $data['email'],
                'name'  => $data['name'],
                // 'password' => \Hash::make($data['api_key']),
                'password' => $data['api_key'],
                'type' => User::TYPE_MERCHANT,

            ]);
            // $user->refresh();
            //    $user->merchant()->create([
            //         'display_name' => $data['name'],
            //         'domain' => $data['domain']
            //         ]);
            $user->merchant()->save($merchant);         
            \DB::commit();

        }catch(Exception $e){
            \DB::rollback();
            dd($e->getMessage());
        }

        return $merchant;

    }

    /**
     * Update the user
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return void
     */
    public function updateMerchant(User $user, array $data)
    {   
        try{
            \DB::beginTransaction();
            $merchant = Merchant::whereUserId($user->id)
            ->update([ 'display_name' => $data['name'],
                       'domain' => $data['domain']]);
            \DB::commit();
        }catch(Exception $e){
            \DB::rollback();
            dd($e->getMessage());
        }
        
    }

    /**
     * Find a merchant by their email.
     * Hint: You'll need to look up the user first.
     *
     * @param string $email
     * @return Merchant|null
     */
    public function findMerchantByEmail(string $email): ?Merchant
    {
        return Merchant::whereHas('user',function(Builder $query) use($email){
            $query->where('email',$email);
        })->first();
    }

    /**
     * Pay out all of an affiliate's orders.
     * Hint: You'll need to dispatch the job for each unpaid order.
     *
     * @param Affiliate $affiliate
     * @return void
     */
    public function payout(Affiliate $affiliate)
    {
        // TODO: Complete this method

        try{
            if($affiliate && isset($affiliate->orders) && !empty($affiliate->orders)){
                // dd($affiliate->orders);
                foreach($affiliate->orders as $order){
                    if($order->payout_status == Order::STATUS_UNPAID){
                        dispatch(new PayoutOrderJob($order));
                    }
                }
                
            }

        }catch(Exception $e){

        }
    }


    public function findMerchantByDomainCode($code): ?Merchant {
       return  Merchant::where('domain',$code)->first();
    }
}
