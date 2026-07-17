<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake('id_ID')->name(),
            'nip' => (string) fake()->unique()->numerify('19########0#####'),
            'password' => Hash::make('password'),
            'is_guest' => false,
            'remember_token' => Str::random(10),
        ];
    }
}
