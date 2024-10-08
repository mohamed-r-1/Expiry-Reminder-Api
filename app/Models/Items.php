<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Items extends Model
{
    use HasFactory;
    protected $fillable = [
        "id", "title", "description", "item_image", "pro_date", "exp_date", "start_reminder","code", "category_id", "quantity"
    ];
    public function  category()
    {
        return  $this->belongsTo(Category::class);
    }


}
