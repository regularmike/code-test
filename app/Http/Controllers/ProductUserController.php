<?php

namespace App\Http\Controllers;

use App\User;
use App\ProductUser;
use Illuminate\Http\Request;

class ProductUserController extends Controller
{
    function store(Request $request, User $user)
    {        
        $this->authorize('create', ProductUser::class);
        $user->products()->attach($request->product_id);
        return response(['success' => true], 201);        
    }

    function destroy(User $user, int $productId)
    {                        
        $this->authorize('delete', ProductUser::class);
        $user->products()->detach($productId);
        return response(['success' => true], 204);      
    }

    function show(User $user)
    {
        $this->authorize('view', ProductUser::class);
        return $user->products;
    }
}
