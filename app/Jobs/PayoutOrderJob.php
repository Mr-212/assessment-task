<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\ApiService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PayoutOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        public Order $order,
       
    ) {}

    /**
     * Use the API service to send a payout of the correct amount.
     * Note: The order status must be paid if the payout is successful, or remain unpaid in the event of an exception.
     *
     * @return void
     */
    public function handle(ApiService $apiService)
    {
         //$apiService = new ApiService();
        // $apiService = app()->make(ApiService::class);


        try{
            

            $res = $apiService->sendPayout($this->order->affiliate->user->email, $this->order->commission_owed);
            // TODO: Complete this method
            if(empty($res)) {
                DB::beginTransaction();
                $this->order->payout_status = Order::STATUS_PAID;
                $this->order->save();  
                DB::commit();
            }   
        }catch(RuntimeException $e){
            DB::rollBack();
            throw new RuntimeException();
            dd($e->getMessage());

        }

    }
}
