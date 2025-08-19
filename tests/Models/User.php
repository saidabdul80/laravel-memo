<?php

namespace Saidabdulsalam\LaravelMemo\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

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
