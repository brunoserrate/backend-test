<?php

namespace Database\Factories;

use App\Models\Redirect;
use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Status;

class RedirectFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Redirect::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $stat = Status::first();
        if (!$stat) {
            $stat = Status::factory()->create();
        }

        return [
            'alias' => $this->faker->text($this->faker->numberBetween(5, 100)),
            'code' => $this->faker->text($this->faker->numberBetween(5, 100)),
            'redirect_url' => $this->faker->text($this->faker->numberBetween(5, 50)),
            'query_params' => $this->faker->text($this->faker->numberBetween(5, 50)),
            'status_id' => $stat,
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'updated_at' => $this->faker->date('Y-m-d H:i:s'),
        ];
    }
}
