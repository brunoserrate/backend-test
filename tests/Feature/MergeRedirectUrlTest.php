<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use App\Repositories\RedirectRepository;
use App\Models\Redirect;

class MergeRedirectUrlTest extends TestCase
{
    /**
     * Teste que junta duas origens
     * ex: utm_source=facebook - utm_campaign=ads -> utm_source=facebook&utm_campaign=ads
     *
     * @return void
     */
    public function test_juntando_duas_origens() {
        $queryParamsRequested = [
            "param1" => "value1",
            "param2" => "value2",
        ];
        $valorEsperado = 'https://www.google.com/search?q=laravel&param1=value1&param2=value2';

        $model = app('App\Models\Redirect');

        $model->fill([
            'alias' => 'teste',
            'code' => 'teste',
            'redirect_url' => 'https://www.google.com/search',
            'query_params' => 'q=laravel',
            'status_id' => 1
        ]);


        $urlFinal = app('App\Repositories\RedirectRepository')->montarUrlFinal($model, $queryParamsRequested);

        $this->assertEquals($valorEsperado, $urlFinal);
    }

    /**
     * Priorizando à Request
     *
     * ex: utm_source=instagram - utm_source=facebook&utm_campaign=ads -> utm_source=instagram&utm_campaign=ads
     *
     * @return void
     */
    public function test_priorizando_request() {
        $queryParamsRequested = [
            "utm_source" => "instagram",
            "utm_campaign" => "ads",
        ];
        $valorEsperado = 'https://www.google.com/search?q=laravel&utm_source=instagram&utm_campaign=ads';

        $model = app('App\Models\Redirect');

        $model->fill([
            'alias' => 'teste',
            'code' => 'teste',
            'redirect_url' => 'https://www.google.com/search',
            'query_params' => 'q=laravel&utm_source=facebook',
            'status_id' => 1
        ]);

        $urlFinal = app('App\Repositories\RedirectRepository')->montarUrlFinal($model, $queryParamsRequested);

        $this->assertEquals($valorEsperado, $urlFinal);
    }

    /**
     * Merge ignorando chave vazia na Request
     * ex: utm_source=&utm_campaign=test - utm_source=facebook -> utm_source=facebook&utm_campaign=test
     *
     * @return void
     */
    public function test_merge_ignorando_chave_vazia() {
        $queryParamsRequested = [
            "utm_source" => "",
            "utm_campaign" => "test",
        ];
        $valorEsperado = 'https://www.google.com/search?q=laravel&utm_source=facebook&utm_campaign=test';

        $model = app('App\Models\Redirect');

        $model->fill([
            'alias' => 'teste',
            'code' => 'teste',
            'redirect_url' => 'https://www.google.com/search',
            'query_params' => 'q=laravel&utm_source=facebook',
            'status_id' => 1
        ]);

        $urlFinal = app('App\Repositories\RedirectRepository')->montarUrlFinal($model, $queryParamsRequested);

        $this->assertEquals($valorEsperado, $urlFinal);
    }
}
