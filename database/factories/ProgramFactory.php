<?php

namespace Database\Factories;

use App\Models\Program;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProgramFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Program::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'uuid' => $this->faker->uuid,
            'name' => $this->faker->company,
            'prefix' => 'TT',
            'email' => $this->faker->unique()->safeEmail,
            'passkit_id' => '',
            'generate_passes' => 'Yes',
            'source' => 'api',
            'status' => 'active',
            'password' => '$2y$04$9Dpjha9svKjlNLdFL25VxeAiCawgkCwC4b8WFDvoWEobaCFzSZ9zm', // password

            //            'remember_token' => Str::random(10),
        ];
    }
}
