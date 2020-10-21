<?php

namespace App\Http\Controllers;

use App\Product;
use Illuminate\Http\Request;
use App\Http\Requests\ProductRequest;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    private $imageDir = 'public/product_images';

    public function show(Product $product)
    {
        $this->authorize('view', $product);
        return $product;
    }

    public function index()
    {
        $this->authorize('viewAny', Product::class);
        return Product::orderBy('name')->get();
    }

    public function store(ProductRequest $request)
    {
        $this->authorize('create', Product::class);
        $validated = $request->validated();                

        $product = Product::create($validated);
        if ($request->file('image')) {
            $path = Storage::putFile($this->imageDir, $request->file('image'));                                    
            $product->image = str_replace($this->imageDir . '/', '', $path);
        }
        $product->save();

        return response([
            'success' => true,
            'message' => 'New product created.',
            'id' => $product->id
        ], 201);
    }

    public function update(ProductRequest $request, Product $product)
    {
        $this->authorize('update', $product);
        $validated = $request->validated();

        $product->update($validated);

        return response([
            'success' => true,
            'message' => 'Product updated.'            
        ], 200);
    }

    public function addImage(Request $request, Product $product)
    {
        $this->authorize('update', $product);        
        $path = Storage::putFile($this->imageDir, $request->file('image'));                                    
        $product->image = str_replace($this->imageDir . '/', '', $path);    
        $product->save();        

        return response([
            'success' => true,
            'message' => 'Product image added.',
            'path' => $path
        ], 200);
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);

        $product->delete();

        return response([
            'success' => true,
            'message' => 'Product deleted.'            
        ], 200);
    }
}
