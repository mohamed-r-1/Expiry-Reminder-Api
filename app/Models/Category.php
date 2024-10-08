<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Items;

class Category extends Model
{
    use HasFactory;
    protected $fillable = [
        "id", "title", "type"
    ];

    public function items()
    {
        return $this->hasMany(items::class, 'category_id');
    }
}
