<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'mobile', 'alternate_mobile','area','address','machine_number','machine_code','machine_model','warranty_status','date_of_inst','month','status'];

    public function customer()
    {
        return $this->hasOne(Areas::class, 'id', 'area');
    }
}
