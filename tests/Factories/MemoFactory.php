<?php

namespace Saidabdulsalam\LaravelMemo\Tests\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Saidabdulsalam\LaravelMemo\Models\Memo;
use Saidabdulsalam\LaravelMemo\Tests\Models\User;

class MemoFactory extends Factory
{
    protected $model = Memo::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence,
            'content' => $this->faker->paragraph,
            'owner_id' => User::factory(),
            'owner_type' => User::class,
            'office_id' => 1,
            'department_id' => [1],
        ];
    }
}
