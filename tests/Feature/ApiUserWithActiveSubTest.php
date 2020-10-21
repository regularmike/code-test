<?php

namespace Tests\Feature;

use App\User;
use App\Product;
use Carbon\Carbon;
use Tests\TestCase;
use App\Subscription;
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApiUserWithActiveSubTest extends TestCase
{    
    use RefreshDatabase;

    private $user, $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            'UserSeeder',
            'ProductSeeder'
        ]);

        $this->user = $this->getUserWithActiveSub();        
        Passport::actingAs($this->user);

        $this->product = Product::first();        
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

    public function testOneProductCanBeFetched()
    {
        $product = $this->product;        

        $response = $this->getJson("/api/products/{$product->id}");

        $response
            ->assertStatus(200)
            ->assertJson([
                'name' => $product->name
            ]);
    }

    public function testAllProductsCanBeFetched()
    {        
        $productCount = DB::table('products')->count();

        $response = $this->getJson("/api/products");

        $response
            ->assertStatus(200)
            ->assertJsonCount($productCount);
    }

    public function testProductCanBeAddedToUser()
    {     
        $userId = $this->user->id;

        $response = $this->postJson("/api/users/$userId/products", [
            'product_id' => $this->product->id
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('product_user', [
            'product_id' => $this->product->id,
            'user_id' => $userId
        ]);
    }

    public function testProductCanBeRemovedFromUser()
    {     
        $userId = $this->user->id;
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
        $numProducts = 3;
        $userId = $this->user->id;
        $products = Product::take($numProducts)->get();
        $this->user->products()->saveMany($products);

        $response = $this->getJson("/api/users/$userId/products");

        $response
            ->assertStatus(200)            
            ->assertJsonCount($numProducts);
    }

    public function testUserCanNotPerformAdminFunctions()
    {        
        $productId = $this->product->id;

        $newProduct = factory(Product::class)->make();
        $response = $this->postJson("/api/products", $newProduct->toArray());
        $response->assertStatus(403);

        $this->product->name .= ' UPDATED';        
        $response = $this->patchJson("/api/products/$productId", $this->product->toArray());
        $response->assertStatus(403);
        
        $response = $this->patchJson("/api/products/$productId/image");
        $response->assertStatus(403);
        
        $response = $this->deleteJson("/api/products/$productId");
        $response->assertStatus(403);                
    }
}
