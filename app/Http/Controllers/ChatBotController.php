<?php
namespace App\Http\Controllers;

require_once("/home/dh_8dcy8x/kentcrm.fugital.com/vendor/twilio/sdk/src/Twilio/autoload.php");


use App\Models\Customer;
use App\Models\CaseType;
use App\Models\Chat;
use App\Models\Cases;

use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Twilio\Rest\Client; 
use Log;

class ChatBotController extends Controller
{
    public function listenToReplies(Request $request)
    {
        $data = $request->All();
        Log::Info($data);
        $message = "";
        $from = $request->input('From');
        $from = str_replace("whatsapp:+","",$from);
        $body = $request->input('Body');
        $customer = Customer::firstWhere('mobile', $from);
        //Log::Info($customer);
        $user = Chat::firstWhere('number', $from);
        Log::Info($user);
        $menus = $this->getCaseTypes();
        if(!$user){
                        
            $message = "Hi , I\'m your Kent Assistant 🙂 from KENT House of Purity!\nWhat can I help you with today? Please choose from the options below. Type Menu Number and send.\n\n";
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

                            $case = Cases::latest()->first();
                            $newid = 1000 + ((int) $case->id + 1);
                            $caseid = "KENTRO".$newid;

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
                                $message  .= "[{$i}]. " . $customer->machine_model ."(". $customer->machine_code. ")\n";
                                $i++;
                            }
                                
                        }elseif ($option == 3) {
                            $customer = Customer::where("mobile", $from)->first();
                            $case_type = CaseType::where('title', 'New Installation')->first();

                            $case = Cases::latest()->first();
                            $newid = 1000 + ((int) $case->id + 1);
                            $caseid = "KENTRO".$newid;

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
                $user->status = 'booked_failed';
                $user->chat = "";
                $user->save();

                 $message="Please try with other number which is registered at the time of product purchase. Thank You!";
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

                $case = Cases::latest()->first();
                $newid = 1000 + ((int) $case->id + 1);
                $caseid = "KENTRO".$newid;

                $case = new Cases();
                $case->customer_id = $customer->id;
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

                $case = Cases::latest()->first();
                $newid = 1000 + ((int) $case->id + 1);
                $caseid = "KENTRO".$newid;

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

            $case = Cases::latest()->first();
            $newid = 1000 + ((int) $case->id + 1);
            $caseid = "KENTRO".$newid;

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

            $message = "Hi , I\'m your Kent Assistant 🙂 from KENT House of Purity!\nWhat can I help you with today? Please choose from the options below. Type Menu Number and send.\n\n";
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
        $twilio_whatsapp_number = getenv('TWILIO_WHATSAPP_NUMBER');
        $account_sid = getenv("TWILIO_SID");
        $auth_token = getenv("TWILIO_AUTH_TOKEN");

        $client = new Client($account_sid, $auth_token);
        return $client->messages->create("whatsapp:+".$recipient, array('from' => "whatsapp:$twilio_whatsapp_number", 'body' => $message));
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
                $machines[] = [
                    'id' => $customer->id, 
                    'name' => $customer->name,
                    'machine' => $customer->machine_model,
                    'machine_code' => $customer->machine_code,
                    'warranty_status' => $customer->warranty_status
                ];
            }
            
        }else{
            $machines = false;
        }
        return $machines;
    }

    /**
     * Send Service Reminders to Customer Every Three Months.
     * 
     * */
    public function SendReminder($value='')
    {
        $customers = Customer::where('status', '1')->get();

        if(count($customers)>0){
            foreach ($customers as $customer) {
                $machines[] = [
                    'id' => $customer->id, 
                    'name' => $customer->name,
                    'machine' => $customer->machine_model,
                    'machine_code' => $customer->machine_code,
                    'warranty_status' => $customer->warranty_status
                ];

                // if current date is more than 3 months of date of installation 
            }
            
        }else{
            $machines = false;
        }
    }
}
