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

Route::get('/user', function (Request $request) {
    return $request->user();
});

// recieve whatsapp message
Route::post('/webhook', function(Request $request){
	
    $data = $request->all();
    Log::Info("Message recieve webhook response");
	Log::Info($data);
	Log::Info("================================================");
    //var_dump($data);
    $phone_id = env('PHONE_ID');
    $verify_token = env('VERIFY_TOKEN');
    $params = [];
    
if(isset($data['entry'][0]['changes'][0]['value']['messages'])){
	    $register_call = 'Register Service Call';

	if($message = $data['entry'][0]['changes'][0]['value']['messages'][0]['type'] == 'interactive'){
		$message = $data['entry'][0]['changes'][0]['value']['messages'][0]['interactive']['list_reply']['title'];
	}else{
		$message = $data['entry'][0]['changes'][0]['value']['messages'][0]['text']['body'];
	}
	//return $message . "=" .  $register_call;
    $from_number = $data['entry'][0]['changes'][0]['value']['messages'][0]['from'];
    $message_id = $data['entry'][0]['changes'][0]['value']['messages'][0]['id'];
    $name = $data['entry'][0]['changes'][0]['value']['contacts'][0]['profile']['name'];
    
    $chat = Session::get('chat');
    //return $chat;
    // var_dump($chat);
    //exit();
    $prev_message = "";
    if(isset($chat[$from_number]['prev_message']))
    	$prev_message = $chat[$from_number]['prev_message'];

    $chat[$from_number]['prev_message'] = $message;
    if($message != "menu"){
    	
    	//echo $chat[$from_number]['menu'] . ' ' . $message;

	    if($chat[$from_number]['menu'] == $register_call && strtolower($prev_message) == "yes" && is_numeric($message)){

	    	// on select machine register a case.
	    	// select customer machine.
			$customer = Customer::findOrFail(intval($message));
			$case_type = CaseType::where('title', $chat[$from_number]['menu'])->first();
			//var_dump($customer);
			if($customer){
				$chat[$from_number]['machine'] = $customer->machine_model;

				$case = Cases::latest()->first();
		        $newid = 1000 + ((int) $case->id + 1);
		        $caseid = "KENTRO".$newid;
				
				$case = new Cases();
				$case->customer_id = $customer->id;
				$case->case_number = $caseid;
				$case->case_type = $case_type->id;
				$case->case_status = "OPEN";
				$case->save();
				$chat[$from_number] = [];
				$new_message = "You call is registered successfully. Your request number is ". $caseid .". We will call you shortly. Thank You!";
			}else{
				$new_message = "Sorry, You have input a wrong number.";
			}

			$params = [
		        "messaging_product" => "whatsapp",
		        "to" => $from_number,
		        "type" => "text",
		        "text" => [
		            "body" => $new_message
		        ]
		    ];

	    }else if($chat[$from_number]['menu'] == $register_call && strtolower($message) == "yes"){

	    	// confirm mobile number = yes
	    	// select customer machine.
	    	$chat[$from_number]['confirm_mobile'] = 'yes';
			$customers = Customer::where('mobile', $from_number)->get();
			if(count($customers)>0){
				foreach ($customers as $customer) {
					$machines[] = [
			    		'id' => $customer->id, 
			    		'name' => $customer->name,
			    		'machine' => $customer->machine_model,
			    		'machine_code' => $customer->machine_code,
			    		'warranty_status' => $customer->warranty_status
			    	];
			    }
				$new_message = "Please reply the id number for selected machine.\n";
				//var_dump($machines);
				foreach ($machines as $machine) {
					$new_message .= $machine["id"].". Model:" . $machine["machine"] ."\n". "Warranty Status:".$machine["warranty_status"];
				}
				//$new_message = $machines;
			}else{
				$new_message = "Sorry, But we cant find any record with this number, please provide another";
			}
			$params = [
		        "messaging_product" => "whatsapp",
		        "to" => $from_number,
		        "type" => "text",
		        "text" => [
		            "body" =>$new_message
		        ]
		    ];

		}else if($chat[$from_number]['menu'] == $register_call && strtolower($message) == "no"){ 
			// confirm mobile number = no
			$new_message = "Please provide another number";
			$params = [
		        "messaging_product" => "whatsapp",
		        "to" => $from_number,
		        "type" => "text",
		        "text" => [
		            "body" =>  $new_message
		        ]
		    ];
	    }else{ // ask for confirm mobile number
		
			$chat[$from_number]['menu'] = $message;
			
			$new_message = "Can you please confirm if {$from_number} is your registered mobile number? Type Yes/No.";

			$params = [
		        "messaging_product" => "whatsapp",
		        "to" => $from_number,
		        "type" => "text",
		        "text" => [
		            "body" => $new_message
		        ]
		    ];
		   // return $params;
		}

   	} else { // by default ask to select a menu

		$rows = [];
		$case_types = CaseType::where('status', 1)->orderBy('order')->get();
        foreach ($case_types as $case_type) {
        	$rows[] =  array(
        		'id' => $case_type->id, 
        		'title' => $case_type->title
        	);
        }

        $params = [
	        "messaging_product" => "whatsapp",
	        "to" => $from_number,
	        "type" => "interactive",
	        "interactive" => [
	            "type" => "list",
			    "body" => [
			      "text" => "Hi {$name}, I\'m your Kent Assistant from KENT House of Purity!\nWhat can I help you with today? Please choose from the options. 🙂"
			    ],
			    "action" => [
			      "button" => "MENU",
			      "sections" => [
			        [
			          "title" => "SELECT OPTION",
			          "rows" => $rows
			        ]
			      ]
			    ]
	        ]
	    ];
	    if(!$chat){
	    	$chat[$from_number] = [];
	   	}else{
	    	if(!array_search($from_number, $chat)){
	    		$chat[$from_number] = [];
			    
	    	}
	    }

	}
    Session::put('chat', $chat);
	if($params){
	    $url = env('WHATSAPP_API_URL');
	    Log::Info("Whatsapp Message Request == ");
	    Log::Info($params);
	    $headers = ["Authorization" => "Bearer " . env('WHATSAPP_TOKEN')];
	    if(env('APP_ENV') == "production"){
		    $client = new \GuzzleHttp\Client();
		    $response = $client->request('POST', $url, ["headers" => $headers, "json" => $params]);
			$data = $response->getBody();
			Log::Info("Whatsapp server response");
			Log::Info($data);
		}
	}
	$data = [
		'chat_history' =>  Session::get('chat'),
		'new_message' => $params
	];
}
    return $data;
});



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

// send message to whatsappsend
Route::post('/messages', function(Request $request) {
    // TODO: validate incoming params first!
	$number = '919307697885';
	$chat = Session::get('chat');
    $url = env('WHATSAPP_API_URL');
    $rows = [];
	$case_types = CaseType::where('status', 1)->orderBy('order')->get();
    foreach ($case_types as $case_type) {
    	$rows[] =  array(
    		'id' => $case_type->id, 
    		'title' => $case_type->title
    	);
    }

    $params = [
        "messaging_product" => "whatsapp",
        "to" => $number,
        "type" => "interactive",
        "interactive" => [
            "type" => "list",
		    "body" => [
		      "text" => "Hi, I\'m your Kent Assistant from KENT House of Purity!\nWhat can I help you with today? Please choose from the options. 🙂"
		    ],
		    "action" => [
		      "button" => "MENU",
		      "sections" => [
		        [
		          "title" => "SELECT OPTION",
		          "rows" => $rows
		        ]
		      ]
		    ]
        ]
    ];
    if(!$chat){
    	$chat[$number] = [];
   	}else{
    	if(!array_search($number, $chat)){
    		$chat[$number] = [];
    	}
    }
    Session::put('chat', $chat);
    $url = env('WHATSAPP_API_URL');
    
    $headers = ["Authorization" => "Bearer " . env('WHATSAPP_TOKEN')];

    $client = new \GuzzleHttp\Client();
    $response = $client->request('POST', $url, ["headers" => $headers, "json" => $params]);
	$data = $response->getBody();
	Log::Info($data);
    return $data;
});