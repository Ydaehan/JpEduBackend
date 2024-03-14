<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Manager;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Manager>
 */
class ManagerFactory extends Factory
{
    protected $model = Manager::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nickname' => 'Admin',
            'email' => 'a01068089833@gmail.com',
            'password' => Hash::make('admin'),
            'phone' => '01012345678',
            'birthday' => '2000-05-19',
            'role' => 'admin'
        ];
    }
}
