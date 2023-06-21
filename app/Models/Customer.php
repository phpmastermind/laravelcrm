<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'mobile', 'alternate_mobile','area','address','machine_number','machine_code','machine_model','warranty_status','date_of_inst','month','status','last_service_date'];

    public function customer()
    {
        return $this->hasOne(Areas::class, 'id', 'area');
    }

    public static function getCustomersWithLastServiceDoneThreeMonthsBefore() {
        $threeMonthsAgo = Carbon::now()->subMonths(3);
        $sevenDaysAgo = Carbon::now()->subDays(7);
        return DB::table('customers')->where(function($query) use ($threeMonthsAgo) {
                        $query->where('last_service_date', '<=', $threeMonthsAgo)
                              ->orWhereNull('last_service_date');
                    })
                    ->where(function($query) use ($threeMonthsAgo) {
                        $query->where('date_of_inst', '<=', $threeMonthsAgo)
                              ->orWhereNull('date_of_inst');
                    }) 
                    ->where(function($query) use ($sevenDaysAgo) {
                        $query->where('last_reminder', '<=', $sevenDaysAgo)
                              ->orWhereNull('last_reminder');
                    })
                    ->where('status', '=', '1')
                    ->get();
    }
}
