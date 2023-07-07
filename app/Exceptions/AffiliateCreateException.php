<?php

namespace App\Exceptions;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AffiliateCreateException extends Exception
{   
        public function render(){
           
        }

        public function report($error){
            Log::error($error);
        }


}
