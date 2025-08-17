<?php

namespace Saidabdulsalam\LaravelMemo\Tests\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Saidabdulsalam\LaravelMemo\Tests\Models\User;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => bcrypt('password'),
            'office_id' => 1,
            'department_id' => 1,
        ];
    }
}
