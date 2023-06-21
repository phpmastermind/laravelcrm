<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('pp', function () {
    return view('privacypolicy');
});

Route::get('/chatbot', 'App\Http\Controllers\ChatBotController@listenToReplies');

// send message to whatsappsend
Route::get('/messages', function(Request $request) {
    // TODO: validate incoming params first!
	$number = '919307697885';
    $url = env('WHATSAPP_API_URL');

    $params = [
    	"messaging_product" => "whatsapp",
    	"to" => $number,
    	"type" => "template",
        "template" => [
        	"name" => "hello_world",
        	"language" => [
                "code" => "en_US"
            ]
        ]
    ];
    $headers = ["Authorization" => "Bearer " . env('WHATSAPP_TOKEN')];

    $client = new \GuzzleHttp\Client();
    $response = $client->request('POST', $url, ["headers" => $headers, "json" => $params]);
    $data = $response->getBody();
    Log::Info($data);

});


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
//Auth::routes();

//Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
