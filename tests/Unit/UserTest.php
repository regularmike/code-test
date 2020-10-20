<?php

namespace Tests\Unit;

use App\User;
use Carbon\Carbon;
use Tests\TestCase;
use App\Subscription;

class UserTest extends TestCase
{    
    private $user;

    protected function setUp() : void
    {
        parent::setUp();
        
        $this->user = factory(User::class)->make();     
        $subscription = factory(Subscription::class)->make([
            'user_id' => null // avoid saving a new user in the db
        ]);
        $this->user->setRelation('subscription', $subscription);        
    }

    public function testSubscriptionNotActiveIfEndInPast() : void
    {           
        $subscription = $this->user->subscription;
        $subscription->start = Carbon::now()->addYears(-2);
        $subscription->end = Carbon::now()->addYears(-1);                
        $this->assertFalse($this->user->hasActiveSubscription);
    }

    public function testSubscriptionNotActiveIfStartInFuture() : void
    {
        $subscription = $this->user->subscription;
        $subscription->start = Carbon::now()->addDays(5);
        $subscription->end = Carbon::now()->addYear();  
        $this->assertFalse($this->user->hasActiveSubscription);
    }

    public function testSubscriptionIsActiveIfNowBetweenStartAndEnd() : void
    {
        $subscription = $this->user->subscription;
        $subscription->start = Carbon::now()->addDays(-5);
        $subscription->end = Carbon::now()->addYear();  
        $this->assertTrue($this->user->hasActiveSubscription);
    }
}
