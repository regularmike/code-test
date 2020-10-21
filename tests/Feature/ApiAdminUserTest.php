<?php

namespace Tests\Feature;

use App\User;
use App\Product;
use Carbon\Carbon;
use Tests\TestCase;
use App\Subscription;
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

class ApiAdminUserTest extends TestCase
{    
    use RefreshDatabase;

    private $user, $product, $imgDirName;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            'UserSeeder',
            'ProductSeeder'
        ]);

        $this->user = User::where('is_admin', true)->first();
        Passport::actingAs($this->user);

        $this->product = Product::first();   
        $this->imgDirName = 'product_images';   
        Storage::fake($this->imgDirName);  
    }    

    public function testAdminCanAddProductWithoutImage()
    {
        $product = factory(Product::class)->make();

        $response = $this->postJson("/api/products", $product->toArray());

        $response->assertStatus(201);
        $this->assertDatabaseHas('products', [
            'name' => $product->name,
            'description' => $product->description,
            'price' => $product->price
        ]);
    }    

    public function testAdminCanAddProductWithImage()
    {        
        $product = factory(Product::class)->make();
        $filename = str_replace(' ', '_', $product->name) . '.jpg';
        $product->image = UploadedFile::fake()->image($filename);

        $response = $this->postJson("/api/products", $product->toArray());

        $response->assertStatus(201);
        $this->assertDatabaseHas('products', [
            'name' => $product->name,
            'description' => $product->description,
            'price' => $product->price,
            'image' => $product->image
        ]);
        Storage::disk($this->imgDirName)->assertExists($filename);
    }    

    public function testNameIsRequired()
    {
        $product = factory(Product::class)->make(['name' => null]);                

        $response = $this->postJson("/api/products", $product->toArray());

        $response->assertStatus(400)
                 ->assertJson([
                    'message' => 'Product name is required.'
                 ]);
        $this->assertDatabaseMissing('products', [
            'name' => null
        ]);
    }    

    public function testDescriptionIsRequired()
    {
        $product = factory(Product::class)->make(['description' => null]);                

        $response = $this->postJson("/api/products", $product->toArray());

        $response->assertStatus(400)
                 ->assertJson([
                    'message' => 'Product description is required.'
                 ]);
        $this->assertDatabaseMissing('products', [
            'description' => null
        ]);
    }    

    public function testPriceIsRequired()
    {
        $product = factory(Product::class)->make(['price' => null]);                

        $response = $this->postJson("/api/products", $product->toArray());

        $response->assertStatus(400)
                 ->assertJson([
                    'message' => 'Product price is required.'
                 ]);
        $this->assertDatabaseMissing('products', [
            'price' => null
        ]);
    }    

    public function testAdminCanUpdateProduct()
    {
        $product = $this->product;
        $product->name .= ' UPDATED';

        $response = $this->patchJson("/api/products/{$product->id}", ['name' => $product->name]);

        $response->assertStatus(204);
        $this->assertDatabaseHas('products', [
            'name' => $product->name            
        ]);

    }    

    public function testAdminCanDeleteProduct()
    {
        $productId = $this->product->id;

        $response = $this->deleteJson("/api/products/$productId");

        $response->assertStatus(204);                 
        $this->assertDatabaseMissing('products', [
            'id' => $productId
        ]);
    }    

    public function testAdminCanUploadProductImage()
    {
        $product = $this->product;        
        $filename = str_replace(' ', '_', $product->name) . '.jpg';
        $image = UploadedFile::fake()->image($filename);                

        $response = $this->patchJson("/api/products/{$product->id}/image", ['image' => $image]);

        $response->assertStatus(204);                 
        Storage::disk($this->imgDirName)->assertExists($filename);
        $this->assertDatabaseHas('products', [
            'image' => $image
        ]);
    }        
}
