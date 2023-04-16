<?php

namespace App\Imports;

use App\Models\Customer;
use App\Models\Areas;

use Maatwebsite\Excel\Concerns\ToModel;

class ImportCustomer implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $area = Areas::firstOrCreate(array('title'=>$row[3]), ['status'=> '1'] );
        $customer = Customer::where('machine_number', $row[5])->get();
        if(count($customer)==0){
            return new Customer([
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
                'last_service_date' => (isset($row[11]))?$row[11]:null,
                'status' => (isset($row[12]))?$row[12]:'1'
            ]);
        }
    }
}
