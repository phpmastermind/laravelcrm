<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Encore\Admin\Traits\DefaultDatetimeFormat;
use Encore\Admin\Traits\ModelTree;

class Areas extends Model
{
    use HasFactory;
    use ModelTree;

    protected $table="areas";

    protected $fillable = ['parent_id', 'title', 'area_code','order'];

}
