<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\Items;

class CategoryController extends Controller
{
    public function getCategoriesWithItems()
    {
        $categories = Category::with('items')->get();
        return response()->json($categories);
    }
}
