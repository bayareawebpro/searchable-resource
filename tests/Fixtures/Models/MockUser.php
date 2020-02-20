<?php

namespace BayAreaWebPro\SearchableResource\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;

class MockUser extends Model
{
    protected $table = 'users';

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

