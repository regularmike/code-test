<?php

namespace App\Http\Controllers;

use App\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function show(Product $product)
    {
        $this->authorize('view', $product);
        return $product;
    }

    public function index()
    {
        $this->authorize('viewAny', Product::class);
        return Product::all();
    }
}
