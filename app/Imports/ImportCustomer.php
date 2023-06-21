<?php

namespace App\Imports;

use App\Models\Customer;
use App\Models\Areas;
use GuzzleHttp\Exception\RequestException;

use Maatwebsite\Excel\Concerns\ToModel;
use Log;

class ImportCustomer implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $area = Areas::firstOrCreate(
            ['title'=>strtoupper($row[3])],
            ['area_code'=>$row[11]],  
        );

        if($row[5]==""){
            $cs = Customer::latest()->first();
            $newid = date("Ymd") + ((int) $cs->id + 1);
            $row[5] = "KR".$newid;
        }
        $customer = Customer::where('machine_number', $row[5])->get();
        if(count($customer)==0){

            $customer = new Customer([
                'name' => $row[0],
                'mobile' => $row[1],
                'alternate_mobile' => $row[2],
                'area' => $area->id,
                'address' => $row[4],
                'machine_number' => $row[5],
                'machine_code' => $row[6],
                'machine_model' => $row[7],
                'warranty_status' => $row[8],
                'date_of_inst' => date("Y-m-d", strtotime($row[9])),
                'month' => $row[10],
                'last_service_date' => (isset($row[12]))?date("Y-m-d",strtotime($row[12])):null,
                'status' => (isset($row[13]))?$row[13]:'1'
            ]);




            $url = env('WHATSAPP_API_URL');
            $headers = ["Authorization" => "Bearer " . env('WHATSAPP_TOKEN')];
            $params = [
                "messaging_product" => "whatsapp",
                "to" => "91". $customer->mobile,
                "type" => "template",
                "template" => [
                    "name" => 'welcome_message',
                    "language" => [
                        "code" => "en_US"
                    ],
                    "components" => [
                        [
                            "type" => "body",
                            "parameters" => [
                                ["type" => "text", "text" => $customer->name ]
                            ]
                        ]
                    ]
                ]
            ];
            if(env('APP_ENV') == "production"){
                $client = new \GuzzleHttp\Client();
                $response = $client->request('POST', $url, ["headers" => $headers, "json" => $params]);
                $data = $response->getBody();
                Log::Info("Whatsapp server response");
                Log::Info($data);
            }
            return $customer;
        }
    }
}
