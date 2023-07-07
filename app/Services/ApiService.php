<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Merchant;
use Exception;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * You don't need to do anything here. This is just to help
 */
class ApiService
{
    /**
     * Create a new discount code for an affiliate
     *
     * @param Merchant $merchant
     *
     * @return array{id: int, code: string}
     */
    public function createDiscountCode(Merchant $merchant): array
    {
        return [
            'id' => rand(0, 100000),
            'code' => Str::uuid()
        ];
    }

    /**
     * Send a payout to an email
     *
     * @param  string $email
     * @param  float $amount
     * @return void
     * @throws RuntimeException
     */
    public function sendPayout(string $email, float $amount)
    {
       
        try{
            if(!empty($email) && !empty($amount)){
                $data = ['amount' => $amount];
                Mail::send('view.name', $data, function($message) use($email){
                    $message->to($email);
                    $message->subject('Payout Notification Alert!');
                });
                return true;
            }

        }catch(Exception $e){
            //dd($e->getMessage());
            throw new RuntimeException($e->getMessage());
        }
        catch(RuntimeException $e){
            throw new RuntimeException($e->getMessage());
        }

        
    }
}
