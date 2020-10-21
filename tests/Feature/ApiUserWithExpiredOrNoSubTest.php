<?php

namespace Tests\Feature;

use App\User;
use App\Product;
use Carbon\Carbon;
use Tests\TestCase;
use App\Subscription;
use Laravel\Passport\Passport;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApiUserWithExpiredOrNoSubTest extends TestCase
{    
    use RefreshDatabase;

    private $product, $newProduct;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            'UserSeeder',
            'ProductSeeder'
        ]);
                         
        $this->product = Product::first();        
        $this->newProduct = factory(Product::class)->make();
    }    

    protected function getUserWithExpiredSub(): User
    {
        $now = Carbon::now();
        // this should give us a non-admin user without any products assigned yet
        $subscription = Subscription::where('start', '>', $now)
                                    ->orWhere('end', '<', $now)
                                    ->latest()
                                    ->first();
        return $subscription->user;
    }
    
    protected function makeRequests(User $user): void
    {
        Passport::actingAs($user);
        $productId = $this->product->id;
        $userId = $user->id;

        $response = $this->getJson("/api/products/$productId");
        $response->assertStatus(403);

        $response = $this->getJson("/api/products");
        $response->assertStatus(403);

        $response = $this->postJson("/api/users/$userId/products", [
            'product_id' => $this->product->id
        ]);
        $response->assertStatus(403);
        
        $response = $this->deleteJson("/api/users/$userId/products/$productId");
        $response->assertStatus(403);

        $response = $this->getJson("/api/users/$userId/products");
        $response->assertStatus(403);

        // admin routes
        $response = $this->postJson("/api/products", $this->newProduct->toArray());
        $response->assertStatus(403);

        $this->product->name .= ' UPDATED';
        $response = $this->patchJson("/api/products/$productId", $this->product->toArray());
        $response->assertStatus(403);
        
        $response = $this->patchJson("/api/products/$productId/image");
        $response->assertStatus(403);
        
        $response = $this->deleteJson("/api/products/$productId");
        $response->assertStatus(403);                
    }

    public function testUserWithExpiredSubCantMakeRequests(): void
    {
        $user = $this->getUserWithExpiredSub();                        
        $this->makeRequests($user);
    }

    public function testUserWithNoSubCantMakeRequests(): void
    {
        $user = User::doesntHave('subscription')->first();                        
        $this->makeRequests($user);
    }    
}
