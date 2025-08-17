<?php

namespace Saidabdulsalam\LaravelMemo\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'office_id',
        'department_id',
    ];

    protected static function newFactory()
    {
        return \Saidabdulsalam\LaravelMemo\Tests\Factories\UserFactory::new();
    }
}
