<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Encore\Admin\Traits\DefaultDatetimeFormat;
use Encore\Admin\Traits\ModelTree;

class Article extends Model
{
    use HasFactory;
    use ModelTree;

    public function article()
    {
        return $this->hasOne(ArticleType::class, 'id', 'type');
    }
}
