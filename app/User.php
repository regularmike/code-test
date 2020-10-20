<?php

namespace App;

use Carbon\Carbon;
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    
    /**
     * The products that belong to the user.
     */
    public function products()
    {
        return $this->belongsToMany('App\Product');
    }

    /**
     * The subscription that belongs to the user.
     */
    public function subscription()
    {
        return $this->hasOne('App\Subscription');
    }

    public function getHasActiveSubscriptionAttribute() 
    {
        $sub = $this->subscription;        
        return Carbon::now()->between($sub->start, $sub->end);
    }
}
