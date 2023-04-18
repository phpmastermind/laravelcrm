<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\CaseType;
use App\Models\Customer;
use App\Models\Engineer;
use App\Models\Cases;



/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/webhook', 'App\Http\Controllers\ChatBotController@listenToReplies');
// recieve whatsapp message
Route::get('/webhook', function(Request $request){

    $data = $request->all();
    $verify_token =  env('VERIFY_TOKEN');
   
    $mode = $data['hub_mode'];
    $token =$data['hub_verify_token'];
    $challenge =$data['hub_challenge'];

    Log::Info($data);

    if($mode && $token){
        if($mode == 'subscribe' && $token == $verify_token){
             $response = response($challenge, 200);
        }else{
             $response = response('Denied', 403);
        }
    }else{
         $response = response('mode empty', 403);
    }

    Log::Info($response);
    return $response;
});


//Route::post('/chatbot/send', 'App\Http\Controllers\ChatBotController@sendWhatsappMessage');

Route::get('/chatbot/sendreminders', 'App\Http\Controllers\ChatBotController@SendServiceReminder');