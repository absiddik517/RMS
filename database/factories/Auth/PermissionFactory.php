<?php

namespace Database\Factories\Auth;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Auth\Permission;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\=Permission>
 */
class PermissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
      return [
        'name' => $this->faker->name(),
        'group_name' => $this->faker->name(),
        'guard_name' => $this->faker->name(),
      ];
    }
}
