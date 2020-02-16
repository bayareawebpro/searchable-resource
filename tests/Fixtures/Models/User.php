<?php

namespace BayAreaWebPro\SearchableResource\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    public $fillable = [
        'name',
        'email',
        'password',
        'remember_token',
        'email_verified_at',
    ];

    public function getTestAttribute(){
        return true;
    }
}

