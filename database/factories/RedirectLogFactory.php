<?php

namespace Database\Factories;

use App\Models\RedirectLog;
use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Redirect;

class RedirectLogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = RedirectLog::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $redirect = Redirect::first();
        if (!$redirect) {
            $redirect = Redirect::factory()->create();
        }

        return [
            'redirect_id' => $redirect->id,
            'ip_request' => $this->faker->text($this->faker->numberBetween(5, 15)),
            'user_agent_request' => $this->faker->text($this->faker->numberBetween(5, 50)),
            'header_referer_request' => $this->faker->text($this->faker->numberBetween(5, 50)),
            'query_param_request' => $this->faker->text($this->faker->numberBetween(5, 50)),
            'access_at' => $this->faker->date('Y-m-d H:i:s'),
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'updated_at' => $this->faker->date('Y-m-d H:i:s'),
        ];
    }
}
