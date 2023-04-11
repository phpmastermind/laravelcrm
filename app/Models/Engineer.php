<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Engineer extends Model
{
    use HasFactory;

    public function engineer()
    {
        return $this->hasOne(Areas::class, 'id', 'area');
    }
}
