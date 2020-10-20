<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class ProductUserController extends Controller
{
    function create(Request $request, User $user)
    {
        $user->products()->attach($request->product_id);
        return response(['success' => true], 201);        
    }

    function destroy(User $user, int $productId)
    {
        $user->products()->detach($productId);
        return response(['success' => true], 204);      
    }

    function show(User $user)
    {
        return $user->products;
    }
}
