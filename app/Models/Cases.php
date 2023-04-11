<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cases extends Model
{
    use HasFactory;

    protected $casts = [
        'extra' => 'json',
    ];

    public function casetype()
    {
        return $this->hasOne(CaseType::class, 'id','case_type');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function engineer()
    {
        return $this->belongsTo(Engineer::class);
    }
}
