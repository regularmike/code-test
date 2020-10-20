<?php

namespace Tests\Feature;

use App\User;
use App\Product;
use Carbon\Carbon;
use Tests\TestCase;
use App\Subscription;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Passport;

class ApiTest extends TestCase
{    
    use RefreshDatabase;

    private $userWithActiveSub, $userWithExpiredSub, $userWithNoSub, $adminUser, 
            $product, $productCount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            'UserSeeder',
            'ProductSeeder'
        ]);

        $this->userWithActiveSub = $this->getUserWithActiveSub();
        $this->userWithExpiredSub = $this->getUserWithExpiredSub();
        $this->userWithNoSub = User::doesntHave('subscription')->first();
        $this->adminUser = User::where('is_admin', true)->first();

        $this->product = Product::first();
        $this->productCount = DB::table('products')->count();
    }

    protected function getUserWithActiveSub(): User
    {
        $now = Carbon::now();
        $subscription = Subscription::where([
            ['start', '<', $now],
            ['end', '>', $now]
        ])->first();
        return $subscription->user;
    }

    protected function getUserWithExpiredSub(): User
    {
        $now = Carbon::now();
        // this should give us a user without any products assigned yet
        $subscription = Subscription::where('start', '>', $now)
                                    ->orWhere('end', '<', $now)
                                    ->latest()
                                    ->first();
        return $subscription->user;
    }    

    public function testOneProductCanBeFetched()
    {
        $product = $this->product;
        Passport::actingAs($this->userWithActiveSub);

        $response = $this->getJson("/api/products/{$product->id}");

        $response
            ->assertStatus(200)
            ->assertJson([
                'name' => $product->name
            ]);
    }

    public function testAllProductsCanBeFetched()
    {
        Passport::actingAs($this->userWithActiveSub);

        $response = $this->getJson("/api/products");

        $response
            ->assertStatus(200)
            ->assertJsonCount($this->productCount);
    }

    public function testProductCanBeAddedToUser()
    {
        Passport::actingAs($this->userWithActiveSub);
        $userId = $this->userWithActiveSub->id;

        $response = $this->postJson("/api/users/$userId/products", [
            'product_id' => $this->product->id
        ]);

        $response->assertStatus(204);
        $this->assertDatabaseHas('product_user', [
            'product_id' => $this->product->id,
            'user_id' => $userId
        ]);
    }

    public function testProductCanBeRemovedFromUser()
    {
        Passport::actingAs($this->userWithActiveSub);
        $userId = $this->userWithActiveSub->id;
        $productId = $this->product->id;
        $this->postJson("/api/users/$userId/products", [
            'product_id' => $productId
        ]);

        $response = $this->deleteJson("/api/users/$userId/products/$productId");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('product_user', [
            'product_id' => $this->product->id,
            'user_id' => $userId
        ]);
    }

    public function testUserProductsCanBeFetched()
    {        
        Passport::actingAs($this->userWithActiveSub);
        $numProducts = 3;
        $userId = $this->userWithActiveSub->id;
        $products = Product::take($numProducts)->get();
        $this->userWithActiveSub->products()->saveMany($products);

        $response = $this->deleteJson("/api/users/$userId/products");

        $response
            ->assertStatus(200)            
            ->assertJsonCount($numProducts);
    }
}
