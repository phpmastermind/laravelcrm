<?php
namespace App\Http\Controllers;

#require_once("/home/dh_8dcy8x/kentcrm.fugital.com/vendor/twilio/sdk/src/Twilio/autoload.php");


use App\Models\Customer;
use App\Models\CaseType;
use App\Models\Chat;
use App\Models\Cases;

use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
#use Twilio\Rest\Client; 
use Log;

class ChatBotController extends Controller
{
    public function listenToReplies(Request $request)
    {
        $data = $request->All();
        $response = $data['entry'][0]['changes'][0]['value'];
        Log::Info($data);
        Log::Info($response);
        if(isset($data['statuses']) || isset($response['statuses']))
            return;

        $message = "";
        // if using twilio client api
        /*$from = $request->input('From');
        $from = str_replace("whatsapp:+91","",$from);
        $fromName = $request->input('ProfileName');
        $body = $request->input('Body');*/

        // if using whatsapp cloud api
        $from = $response['messages'][0]['from'];
        $from = str_replace("91","",$from);
        $fromName = $response['contacts'][0]['profile']['name'];
        $body = $response['messages'][0]['text']['body'];

        $customer = Customer::firstWhere('mobile', $from);
        //Log::Info($customer);
        $user = Chat::firstWhere('number', $from);
        Log::Info($user);
        $menus = $this->getCaseTypes();
        if(!$user){
                        
            $message = "Hi {$fromName}, I\'m your Kent Assistant 🙂 from KENT House of Purity!\nWhat can I help you with today? Please choose from the options below. Type Menu Number and send.\n\n";
            $i = 1;
            foreach ($menus as $menu) {
               $message .= "[{$i}] . $menu->title" . "\n";
               $i++;
            }
            //$res = $this->sendmessage($message, $from);

            Chat::create([
                "number" => $from, 
                "status" => "main", 
                "chat"   => ""
            ]);

        }elseif($user->status == 'main'){
            try {
                $option = intval($body);
            } catch (Exception $e) {
                $message = "Please enter a valid response";
                $res = $this->sendmessage($message, $from);
                return str($res);
            }

            if($user){
                $chat = json_decode($user->chat, true);
                $chat['menu'] = $option;
                $user->chat = json_encode($chat);
                $user->status = 'confirm_number';
                $user->save();
            }
            $message = "Can you please confirm if {$from} is your registered mobile number? Reply option number.\n\n.";
            $message .= "[1]. Yes\n [2]. No";
            $rs = $this->sendmessage($message,$from);
            return $rs;
            
        }elseif($user->status == 'confirm_number'){
            try {
                $option = intval($body);
            } catch (Exception $e) {
                $message = "Please enter a valid response";
                $res = $this->sendmessage($message, $from);
                return str($res);
            }

            if($option == 1){
               $customers = Customer::where('mobile', $from)->get();
               if(count($customers)>0){
                    if($user){
                        $chat = json_decode($user->chat, true);
                        $option = intval($chat['menu']);

                        if($option == 1){
                            
                            $customer = Customer::where("mobile", $from)->first();
                            $case_type = CaseType::where('title', 'New Product Demo')->first();
                            
                            $caseid = $this->getNewCaseId();
                            $case = new Cases();
                            $case->customer_id = $customer->id;
                            $case->case_number = $caseid;
                            $case->case_type = $case_type->id;
                            $case->case_status = "OPEN";
                            $case->save();

                            $user->status = 'booked';
                            $user->chat = '';
                            $user->save();

                            $message = "Service Booked Successfully. Your Request Id is {$caseid}.\n We will call you shortly. Thank You!";

                        }else if ($option == 2) {
                            
                            $user->status = 'register_service_call';
                            $user->save();
                            
                            $message = "Please select a machine by chossing an option below.\n.";
                            $i=1;
                            foreach ($customers as $customer) {
                                if($customer->machine_model!=""){
                                    $message  .= "[{$i}]. " . $customer->machine_model ."(". $customer->machine_code. ")\n";
                                    $i++;
                                }
                            }
                                
                        }elseif ($option == 3) {
                            $user->status = 'new_installation';
                            $user->save();
                            
                            $message = "Please select a machine by chossing an option below.\n\n";
                            $i=1;
                            foreach ($customers as $customer) {
                                if($customer->machine_model!=""){
                                    $message  .= "[{$i}]. " . $customer->machine_model ."(". $customer->machine_code. ")\n";
                                    $i++;
                                }
                            }
                        }elseif ($option == 4) {
                            // code...
                            $user->status = 're_installation';
                            $user->save();
                            $customer = Customer::where("mobile", $from)->first();
                            $message = "Do you want installation on your current address?.\n.";
                            $message .= "Current Address: ". $customer->address. "\n\n";
                            
                            $message .= "[1]. Current Address\n[2]. Enter New Address";
                        }else{
                            $message  = 'Please enter a valid option.';
                        }

                    }
               }else{
                    $user->status = 'booked_failed';
                    $user->chat = "";
                    $user->save();

                    $message="Sorry, Your number is not regisered with us. \nPlease try with other number which is registered at the time of product purchase. Thank You!";
               }

            }else{
                
                if($user){
                    $chat = json_decode($user->chat, true);
                    $menu = intval($chat['menu']);
                }
                // if new product demo
                if($menu == 1){
                    
                    if(!$customer){
                        $customer = new Customer();
                        $customer->name = $fromName;
                        $customer->mobile = $from;
                        $customer->save();
                    }
                    
                    if($customer){
                        $case_type = CaseType::where('title', 'New Product Demo')->first();
                        $case = new Cases();
                        $caseid = $this->getNewCaseId();
                        $case->customer_id = $customer->id;
                        $case->case_number = $caseid;
                        $case->case_type = $case_type->id;
                        $case->case_status = "OPEN";
                        $case->save();

                        $user->status = 'main';
                        $user->chat = '';
                        $user->save();

                        $message = "Your request is booked successfully. Your Request Id is {$caseid}.\n We will call you shortly. Thank You!";
                    }else{
                        $user->status = 'main';
                        $user->chat = "";
                        $user->save();

                        $message="Sorry, Your request could not booked. Please try again!";
                    }

                }else{

                    $user->status = 'booked_failed';
                    $user->chat = "";
                    $user->save();

                    $message="Please try with other number which is registered at the time of product purchase. Thank You!";
                }
            }
        
        }else if($user->status == 'register_service_call'){

            try {
                $option = intval($body);
            } catch (Exception $e) {
                $message = "Please enter a valid response";
                $res = $this->sendmessage($message, $from);
                return str($res);
            }

            $machines = $this->getMachines($from);
            if($option == 0):
                $user->status = 'main';
                $user->chat = '';
                $user->save();
                $message = "You can choose from one of the options below:\n\n Type Number \n\n";
                $menus = $this->getCaseTypes();
                foreach ($menus as $key => $menu) {$message .= "[".($key+1)."]. " . $menu;}
            elseif($option >= 1 && $option <= count($machines)):
                $customer = Customer::where("mobile", $from)->first();
                $case_type = CaseType::where('title', 'Register Service Call')->first();

                $caseid = $this->getNewCaseId();

                $case = new Cases();
                $case->customer_id = $customer->id;
                if($machine = $machines[((int)$option-1)]){
                    $case->customer_id      = $machine['id'];
                    $case->machine_number   = $machine['machine_number'];
                }
                $case->case_number = $caseid;
                $case->case_type = $case_type->id;
                $case->case_status = "OPEN";
                $case->save();

                $user->status = 'booked';
                $user->chat = '';
                $user->save();

                $message = "Service Booked Successfully. Your Case Id is {$caseid}. \n We will call you shortly. Thank You!";
            else:    
                $message = "Wrong input, Try Again.";
            endif;

        }else if($user->status == 'new_installation'){

            try {
                $option = intval($body);
            } catch (Exception $e) {
                $message = "Please enter a valid response";
                $res = $this->sendmessage($message, $from);
                return str($res);
            }

            $machines = $this->getMachines($from);
            if($option == 0):
                $user->status = 'main';
                $user->chat = '';
                $user->save();
                $message = "You can choose from one of the options below:\n\n Type Number \n\n";
                $menus = $this->getCaseTypes();
                foreach ($menus as $key => $menu) {$message .= "[".($key+1)."]. " . $menu;}
            elseif($option >= 1 && $option <= count($machines)):
                $customer = Customer::where("mobile", $from)->first();
                $case_type = CaseType::where('title', 'New Installation')->first();

                $caseid = $this->getNewCaseId();

                $case = new Cases();
                $case->customer_id = $customer->id;
                if($machine = $machines[((int)$option-1)]){
                    $case->customer_id      = $machine['id'];
                    $case->machine_number   = $machine['machine_number'];
                }
                $case->case_number = $caseid;
                $case->case_type = $case_type->id;
                $case->case_status = "OPEN";
                $case->save();

                $user->status = 'booked';
                $user->chat = '';
                $user->save();

                $message = "Service Booked Successfully. Your Case Id is {$caseid}. \n We will call you shortly. Thank You!";
            else:    
                $message = "Wrong input, Try Again.";
            endif;

        }elseif ($user->status == 're_installation') {
            
            try {
                $option = intval($body);
            } catch (Exception $e) {
                $message = "Please enter a valid response";
                $res = $this->sendmessage($message, $from);
                return str($res);
            }

            $machines = $this->getMachines($from);
            if($option == 0):
                $user->status = 'main';
                $user->chat = '';
                $user->save();
                $message = "You can choose from one of the options below:\n\n Type Number \n\n";
                $menus = $this->getCaseTypes();
                foreach ($menus as $key => $menu) {$message .= "[".($key+1)."]. " . $menu;}
            elseif($option == 1):
                $customer = Customer::where("mobile", $from)->first();
                $case_type = CaseType::where('title', 'Re-Installation')->first();

                $caseid = $this->getNewCaseId();

                $case = new Cases();
                $case->customer_id = $customer->id;
                $case->case_number = $caseid;
                $case->case_type = $case_type->id;
                $case->case_status = "OPEN";
                $address = array("Address" => $customer->address);
                $case->extra = json_encode($address);
                $case->save();

                $user->status = 'booked';
                $user->chat = '';
                $user->save();

                $message = "Service Booked Successfully. Your Request Id is {$caseid} .\n We will call you shortly. Thank You!";
            elseif($option == 2):
                
                $user->status = 'enter_new_address';
                $user->chat = '';
                $user->save();

                $message = "Please enter your new address in one line.";
            else:    
                $message = "Wrong input, Try Again.";
            endif;


        }elseif ($user->status == 'enter_new_address') {

            $customer = Customer::where("mobile", $from)->first();
            $case_type = CaseType::where('title', 'Re-Installation')->first();

            $caseid = $this->getNewCaseId();

            $case = new Cases();
            $case->customer_id = $customer->id;
            $case->case_number = $caseid;
            $case->case_type = $case_type->id;
            $case->case_status = "OPEN";
            $address = array("Address" => $body);
            $case->extra = json_encode($address);
            $case->save();

            $user->status = 'booked';
            $user->chat = '';
            $user->save();

            $message = "Service Booked Successfully. Your Request Id is {$caseid}. \n We will you shortly. Thank You!";

        }elseif ($user->status == 'booked') {
            $user->status = 'main';
            $user->chat = '';
            $user->save();
        
            $message = "Thanks for your contacting again. Please choose from the options below. Type Menu Number and send.\n\n";
            $i = 1;
            foreach ($menus as $menu) {
               $message .= "[{$i}] . $menu->title" . "\n";
               $i++;
            }

        }else{
        
            $user->status = 'main';
            $user->chat = '';
            $user->save();

            $message = "Hi {$fromName}, I\'m your Kent Assistant 🙂 from KENT House of Purity!\nWhat can I help you with today? Please choose from the options below. Type Menu Number and send.\n\n";
            $i = 1;
            foreach ($menus as $menu) {
               $message .= "[{$i}] . $menu->title" . "\n";
               $i++;
            }
        }

        /*if($user){
            $chat = json_decode($user->chat);
            $chat[] = ['text'=>$message, 'datetime' => date('Y/m/d H:i:s')];
            $user->chat = json_encode($chat);
            $user->save();
        }*/

        Log::Info("Message". $message);
        $rs = $this->sendmessage($message,$from);
        Log::Info($rs);
        return $rs;
    }

    public function sendWhatsappMessage(Request $request){
        return $this->sendmessage($request->input('message'),$request->input('from')); 
    }

    /**
     * Sends a WhatsApp message  to user using
     * @param string $message Body of sms
     * @param string $recipient Number of recipient
     */
    public function sendmessage(string $message, string $recipient)
    {
        /*$twilio_whatsapp_number = getenv('TWILIO_WHATSAPP_NUMBER');
        $account_sid = getenv("TWILIO_SID");
        $auth_token = getenv("TWILIO_AUTH_TOKEN");

        $client = new Client($account_sid, $auth_token);
        return $client->messages->create("whatsapp:+91".$recipient, array('from' => "whatsapp:$twilio_whatsapp_number", 'body' => $message));*/

        $url = env('WHATSAPP_API_URL');
        $headers = ["Authorization" => "Bearer " . env('WHATSAPP_TOKEN')];
        $params = [
            "messaging_product" => "whatsapp",
            "to" => "91".$recipient,
            "type" => "text",
            "text" => [
                "body" => $message
            ]
        ];
        if(env('APP_ENV') == "production"){
            $client = new \GuzzleHttp\Client();
            $response = $client->request('POST', $url, ["headers" => $headers, "json" => $params]);
            $data = $response->getBody();
            Log::Info("Whatsapp server response");
            Log::Info($data);
        }
    
    }


    public function getCaseTypes()
    {
        $menus = [];
        $case_types = CaseType::where('status', 1)->orderBy('order')->get();
        foreach ($case_types as $case_type) {
            $menus[] = $case_type->title;
        }

        return $case_types;
    }

    public function getMachines($mobile)
    {
        $customers = Customer::where('mobile', $mobile)->get();

        $machines = [];
        if(count($customers)>0){
            foreach ($customers as $customer) {
                if($customer->machine_number!=""){
                    $machines[] = [
                        'id' => $customer->id, 
                        'name' => $customer->name,
                        'machine' => $customer->machine_model,
                        'machine_code' => $customer->machine_code,
                        'machine_number' => $customer->machine_number,
                        'warranty_status' => $customer->warranty_status
                    ];
                }
            }
        }else{
            $machines = false;
        }
        Log::Info($machines);
        return $machines;
    }

    public function getNewCaseId()
    {
        $case = Cases::latest()->first();
        if($case){
            $newid = 1000 + ((int) $case->id + 1);
         }else{
            $newid = 1001;
         }
       
        $caseid = "KENTRO".$newid;
        return $caseid;
    }

    /**
     * Send Service Reminders to Customer Every Three Months.
     * 
     * */
    public function SendServiceReminder(Request $request)
    {

        $customers = Customer::getCustomersWithLastServiceDoneThreeMonthsBefore();
        $log = "Total Customers number service expiration :". count($customers);
        $x = 0;
        foreach ($customers as $row){
            
            $customer = Customer::find($row->id);
            if($customer){
                $user = Chat::firstWhere('number', $customer->mobile);
                $from = $customer->mobile;
                if(!$user){        
                    Chat::create([
                        "number" => $from, 
                        "status" => "main", 
                        "chat"   => ""
                    ]);
                }else{
                    $user->status = 'main';
                    $user->chat = '';
                    $user->save();
                }
                Log::Info($user);
                $menus = $this->getCaseTypes();
                $message = "Hi {$customer->name} , I\'m your Kent Assistant 🙂 from KENT House of Purity!\nYour R.O water purifier ({$customer->machine_model}) service due is over. Please choose from the options below to book the service. Type option number and send.\n\n";
                $i = 1;
                foreach ($menus as $menu) {
                   $message .= "[{$i}] . $menu->title" . "\n";
                   $i++;
                }
                // send notification on whatsapp
                $this->sendmessage($message, $customer->mobile); 

                $customer->last_reminder = date("Y-m-d H:i:s");
                $customer->save();
                $x++;
            }

        }
        
        $log .= "Total {$x} notification sent on ". date("Y-m-d H:i:s");
        Log::Info($log);
        return response($customers, 200);
    }
}
