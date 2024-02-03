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

    protected $queryParams = [
        'utm_source' => 'facebook',
        'utm_medium' => 'cpc',
        'utm_campaign' => 'black_friday',
        'utm_content' => 'link1',
        'utm_term' => 'term1'
    ];

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

        $queryParams = $this->queryParams;

        return [
            'alias' => $this->faker->text($this->faker->numberBetween(5, 100)),
            'code' => $this->faker->text($this->faker->numberBetween(5, 6)),
            'redirect_url' => $this->faker->url(),
            'query_params' => http_build_query($queryParams),
            'status_id' => $stat,
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'updated_at' => $this->faker->date('Y-m-d H:i:s'),
        ];
    }
}
