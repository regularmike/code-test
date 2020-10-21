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

    private $user, $product, $imgDirName = 'public/product_images';

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
        Storage::fake('local');  
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
        $file = UploadedFile::fake()->image('product_image.jpg');        

        $response = $this->postJson("/api/products", 
            array_merge(            
                $product->toArray(),
                ['image' => $file]
            )
        );                

        $image = $file->hashName();
        $response->assertStatus(201);
        $this->assertDatabaseHas('products', [
            'name' => $product->name,
            'description' => $product->description,
            'price' => $product->price,
            'image' => $image
        ]);
        Storage::disk('local')->assertExists(sprintf('%s/%s', $this->imgDirName, $image));
    }    

    public function testNameIsRequired()
    {
        $product = factory(Product::class)->make(['name' => null]);                

        $response = $this->postJson("/api/products", $product->toArray());

        $response->assertStatus(422)
                 ->assertJson([
                    'errors' => [
                        'name' => ['The name field is required.']
                    ]
                 ]);
        $this->assertDatabaseMissing('products', [
            'name' => null
        ]);
    }    

    public function testDescriptionIsRequired()
    {
        $product = factory(Product::class)->make(['description' => null]);                

        $response = $this->postJson("/api/products", $product->toArray());

        $response->assertStatus(422)
                 ->assertJson([
                    'errors' => [
                        'description' => ['The description field is required.']
                    ]
                 ]);
        $this->assertDatabaseMissing('products', [
            'description' => null
        ]);
    }    

    public function testPriceIsRequired()
    {
        $product = factory(Product::class)->make(['price' => null]);                

        $response = $this->postJson("/api/products", $product->toArray());

        $response->assertStatus(422)
                 ->assertJson([
                    'errors' => [
                        'price' => ['The price field is required.']
                    ]
                 ]);
        $this->assertDatabaseMissing('products', [
            'price' => null
        ]);
    }    

    public function testAdminCanUpdateProduct()
    {
        $product = $this->product;
        $product->name .= ' UPDATED';        

        $response = $this->patchJson("/api/products/{$product->id}", $product->toArray());        

        $response->assertStatus(200);
        $this->assertDatabaseHas('products', [
            'name' => $product->name            
        ]);

    }    

    public function testAdminCanDeleteProduct()
    {
        $productId = $this->product->id;

        $response = $this->deleteJson("/api/products/$productId");

        $response->assertStatus(200);                 
        $this->assertDatabaseMissing('products', [
            'id' => $productId
        ]);
    }    

    public function testAdminCanUploadProductImage()
    {
        $product = $this->product;        
        $file = UploadedFile::fake()->image('product_image.jpg');               
        $image = $file->hashName();

        $response = $this->patchJson("/api/products/{$product->id}/image", ['image' => $file]);

        $response->assertStatus(200);                 
        Storage::disk('local')->assertExists(sprintf('%s/%s', $this->imgDirName, $image));
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'image' => $image
        ]);                
    }        
}
