<?php

use App\User;
use App\Subscription;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // give everyone the same password for easy testing but use random emails 
        $password = Hash::make('password');

        // create some users with active subscriptions
        factory(User::class, 3)
            ->create(['password' => $password])
            ->each(function ($user) {
                $user->subscription()->save(factory(Subscription::class)->make([
                    'start' => Carbon::yesterday(),
                    'end' => Carbon::now()->addYear()
                ]));
            });           

        // create some users with expired subscriptions
        factory(User::class, 3)
            ->create(['password' => $password])
            ->each(function ($user) {
                $user->subscription()->save(factory(Subscription::class)->make([
                    'start' => Carbon::now()->addYear(-1),
                    'end' => Carbon::yesterday()
                ]));
            });   

        // create a user with no subscription at all
        factory(User::class)->create(['password' => $password]);    
        
        // create an admin user if it doesn't already exist
        if (!User::where('email', 'admin@example.com')->exists()) {            
            factory(User::class)->create([
                'email' => 'admin@example.com',
                'password' => $password,
                'is_admin' => true
            ]);    
        }
    }
}
