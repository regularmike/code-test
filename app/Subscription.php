<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    /**
     * The user to whom this subscription belongs.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
