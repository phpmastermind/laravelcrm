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

Route::post('/chatbot', 'App\Http\Controllers\ChatBotController@listenToReplies');


Route::post('/chatbot/send', 'App\Http\Controllers\ChatBotController@sendWhatsappMessage');