<?php

namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;

use App\Models\Redirect;
use App\Models\RedirectLog;
use Hashids\Hashids;

class RedirectApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_criar_url_valida()
    {
        $response = $this->postJson('/api/redirect', [
            'redirect_url' => 'https://www.google.com.br'
        ]);

        $response->assertStatus(200);

        $result = json_decode($response->getContent(), true);

        $this->assertNotEmpty($result['data']['code'], 'Retorno deve conter um código (code)');
        $this->assertArrayNotHasKey('id', $result['data'], 'Retorno não deve conter um id');
    }

    /**
     * @test
     */

    public function test_dns_invalido()
    {
        $response = $this->postJson('/api/redirect', [
            'redirect_url' => 'https://www.extremene-invalidate-dns.con.'
        ]);

        $response->assertStatus(422);

        $result = json_decode($response->getContent(), true);

        $this->assertNotEmpty($result['message'], 'Retorno deve conter uma mensagem de erro');
    }

    /**
     * @test
     */
    public function test_url_invalida()
    {
        $response = $this->postJson('/api/redirect', [
            'redirect_url' => 'invalid_url'
        ]);

        $response->assertStatus(422);

        $result = json_decode($response->getContent(), true);

        $this->assertNotEmpty($result['message'], 'Retorno deve conter uma mensagem de erro');
    }

    /**
     * @test
     */
    public function test_url_igual_a_urL_da_aplicacao()
    {
        $response = $this->postJson('/api/redirect', [
            'redirect_url' => config('app.url') . ':' . config('app.app_port')
        ]);

        $response->assertStatus(422);

        $result = json_decode($response->getContent(), true);

        $this->assertNotEmpty($result['message'], 'Retorno deve conter uma mensagem de erro');
    }

    /**
     * @test
     */
    public function test_url_sem_https()
    {
        $response = $this->postJson('/api/redirect', [
            'redirect_url' => 'http://www.google.com'
        ]);

        $response->assertStatus(422);

        $result = json_decode($response->getContent(), true);

        $this->assertNotEmpty($result['message'], 'Retorno deve conter uma mensagem de erro');
    }

    /**
     * @test
     */
    public function test_url_diferente_de_200_ou_201()
    {
        $response = $this->postJson('/api/redirect', [
            'redirect_url' => 'https://www.google.com/invalid'
        ]);

        $response->assertStatus(422);

        $result = json_decode($response->getContent(), true);

        $this->assertNotEmpty($result['message'], 'Retorno deve conter uma mensagem de erro');
    }

    /**
     * @test
     */
    public function test_url_com_query_params_com_chave_vazia()
    {
        $response = $this->postJson('/api/redirect', [
            'redirect_url' => 'https://www.google.com?param='
        ]);

        $response->assertStatus(422);

        $result = json_decode($response->getContent(), true);

        $this->assertNotEmpty($result['message'], 'Retorno deve conter uma mensagem de erro');
    }

    /**
     * @test
     */
    public function test_estatisticas_de_acesso()
    {
        $redirect = Redirect::factory()->create();

        $redirect->code = (new Hashids(config('hashid.key'), config('hashid.length')))->encode($redirect->id);

        $redirect->save();

        for ($i = 0; $i < 10; $i++) {

            $redirectLog = RedirectLog::factory()
                ->create(
                    [
                        'redirect_id' => $redirect->id,
                        'ip_request' => '192.168.0.0',
                        'user_agent_request' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36',
                        'header_referer_request' => '',
                        'query_param_request' => json_encode([
                            'utm_source' => 'facebook',
                            'utm_medium' => 'cpc',
                            'utm_campaign' => 'black_friday',
                            'utm_content' => 'link1',
                            'utm_term' => 'term1'
                        ]),
                        'access_at' => now()->subMinutes($i),
                    ],
                );
            $redirectLog->save();
        }

        $redirectLog = RedirectLog::factory()
            ->create(
                [
                    'redirect_id' => $redirect->id,
                    'ip_request' => '185.178.0.0',
                    'user_agent_request' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36',
                    'header_referer_request' => '',
                    'query_param_request' => json_encode([
                        'utm_source' => 'facebook',
                        'utm_medium' => 'cpc',
                        'utm_campaign' => 'black_friday',
                        'utm_content' => 'link1',
                        'utm_term' => 'term1'
                    ]),
                    'access_at' => now()
                ],
            );
        $redirectLog->save();

        $response = $this->getJson('/api/redirect/' . $redirect->code . '/stats');

        $response->assertStatus(200);

        $result = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('total_acessos', $result['data'], 'Retorno deve conter o total de acessos');
        $this->assertArrayHasKey('total_acessos_unicos', $result['data'], 'Retorno deve conter o total de acessos únicos');
        $this->assertArrayHasKey('top_referrers', $result['data'], 'Retorno deve conter os top referrers');
        $this->assertArrayHasKey('acessados_ultimos_dias', $result['data'], 'Retorno deve conter os acessos dos últimos dias');

        $this->assertSame(11, $result['data']['total_acessos'], 'Total de acessos deve ser 11');
        $this->assertSame(2, $result['data']['total_acessos_unicos'], 'Total de acessos únicos deve ser 2');
    }

    /**
     * @test
     */
    public function test_estatisticas_de_acesso_com_referers()
    {
        $redirect = Redirect::factory()->create();

        $redirect->code = (new Hashids(config('hashid.key'), config('hashid.length')))->encode($redirect->id);

        $redirect->save();

        for ($i = 0; $i < 30; $i++) {

            $redirectLog = RedirectLog::factory()
                ->create(
                    [
                        'redirect_id' => $redirect->id,
                        'ip_request' => '192.168.0.0',
                        'user_agent_request' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36',
                        'header_referer_request' => 'https://www.google.com',
                        'query_param_request' => json_encode([
                            'utm_source' => 'facebook',
                            'utm_medium' => 'cpc',
                            'utm_campaign' => 'black_friday',
                            'utm_content' => 'link1',
                            'utm_term' => 'term1'
                        ]),
                        'access_at' => now()->subMinutes($i),
                    ],
                );
            $redirectLog->save();
        }

        for ($i = 0; $i < 10; $i++) {

            $redirectLog = RedirectLog::factory()
                ->create(
                    [
                        'redirect_id' => $redirect->id,
                        'ip_request' => '192.168.0.0',
                        'user_agent_request' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36',
                        'header_referer_request' => 'https://www.facebook.com',
                        'query_param_request' => json_encode([
                            'utm_source' => 'facebook',
                            'utm_medium' => 'cpc',
                            'utm_campaign' => 'black_friday',
                            'utm_content' => 'link1',
                            'utm_term' => 'term1'
                        ]),
                        'access_at' => now()->subMinutes($i),
                    ],
                );
            $redirectLog->save();
        }

        $response = $this->getJson('/api/redirect/' . $redirect->code . '/stats');

        $response->assertStatus(200);

        $result = json_decode($response->getContent(), true);

        $this->assertSame($result['data']['top_referrers'][0]['referer'], 'https://www.google.com', 'Top referer deve ser https://www.google.com');
    }

    /**
     * @test
     */
    public function test_estatisticas_de_acesso_dos_ultimos_10_dias()
    {
        $redirect = Redirect::factory()->create();

        $redirect->code = (new Hashids(config('hashid.key'), config('hashid.length')))->encode($redirect->id);

        $redirect->save();

        $rangeDias = 2;
        $countDays = 0;
        $data = now();

        for ($i = 0; $i < 20; $i++) {

            $countDays++;

            if ($countDays % $rangeDias == 0) {
                $data = $data->subDays(1);

                $countDays = 0;
                $rangeDias++;
            }

            $redirectLog = RedirectLog::factory()
                ->create(
                    [
                        'redirect_id' => $redirect->id,
                        'ip_request' => '192.168.0.0',
                        'user_agent_request' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36',
                        'header_referer_request' => 'https://www.google.com',
                        'query_param_request' => json_encode([
                            'utm_source' => 'facebook',
                            'utm_medium' => 'cpc',
                            'utm_campaign' => 'black_friday',
                            'utm_content' => 'link1',
                            'utm_term' => 'term1'
                        ]),
                        'access_at' => $data,
                        'created_at' => $data,
                        'updated_at' => $data,
                    ],
                );
            $redirectLog->save();
        }

        $response = $this->getJson('/api/redirect/' . $redirect->code . '/stats');

        $response->assertStatus(200);

        $result = json_decode($response->getContent(), true);

        $this->assertLessThanOrEqual(10, count($result['data']['acessados_ultimos_dias']), 'Deve retornar menos de 10 dias');
        $this->assertSame(6, $result['data']['acessados_ultimos_dias'][0]['total'], 'Total de acessos do primeiro dia deve ser 6');
    }

    /**
     * @test
     */
    public function test_estatisticas_de_quando_nao_ha_acesso_dos_ultimos_10_dias()
    {
        $redirect = Redirect::factory()->create();

        $redirect->code = (new Hashids(config('hashid.key'), config('hashid.length')))->encode($redirect->id);

        $redirect->save();

        $rangeDias = 2;
        $countDays = 0;
        $data = now()->subDays(20);

        for ($i = 0; $i < 20; $i++) {

            $countDays++;

            if ($countDays % $rangeDias == 0) {
                $data = $data->subDays(2);

                $countDays = 0;
                $rangeDias++;
            }

            $redirectLog = RedirectLog::factory()
                ->create(
                    [
                        'redirect_id' => $redirect->id,
                        'ip_request' => '192.168.0.0',
                        'user_agent_request' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36',
                        'header_referer_request' => 'https://www.google.com',
                        'query_param_request' => json_encode([
                            'utm_source' => 'facebook',
                            'utm_medium' => 'cpc',
                            'utm_campaign' => 'black_friday',
                            'utm_content' => 'link1',
                            'utm_term' => 'term1'
                        ]),
                        'access_at' => $data,
                        'created_at' => $data,
                        'updated_at' => $data,
                    ],
                );
            $redirectLog->save();
        }

        $response = $this->getJson('/api/redirect/' . $redirect->code . '/stats');

        $response->assertStatus(200);

        $result = json_decode($response->getContent(), true);

        $this->assertEquals(0, count($result['data']['acessados_ultimos_dias']), 'Deve retornar 0 dias');

        $ultimoDia = now()->sub(10, 'days')->format('Y-m-d');

        foreach ($result['data']['acessados_ultimos_dias'] as $acesso) {
            $this->assertLessThan($ultimoDia, $acesso['date'], 'Deve retornar os acessos dos últimos 10 dias');
        }
    }

    public function test_estatisticas_de_quando_ha_acesso_dos_ultimos_10_dias()
    {
        $redirect = Redirect::factory()->create();

        $redirect->code = (new Hashids(config('hashid.key'), config('hashid.length')))->encode($redirect->id);

        $redirect->save();

        $data = now();

        for ($i = 0; $i < 20; $i++) {
            $redirectLog = RedirectLog::factory()
                ->create(
                    [
                        'redirect_id' => $redirect->id,
                        'ip_request' => '192.168.0.0',
                        'user_agent_request' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36',
                        'header_referer_request' => 'https://www.google.com',
                        'query_param_request' => json_encode([
                            'utm_source' => 'facebook',
                            'utm_medium' => 'cpc',
                            'utm_campaign' => 'black_friday',
                            'utm_content' => 'link1',
                            'utm_term' => 'term1'
                        ]),
                        'access_at' => $data,
                        'created_at' => $data,
                        'updated_at' => $data,
                    ],
                );
            $redirectLog->save();

            $data->subDays(1);
        }

        $response = $this->getJson('/api/redirect/' . $redirect->code . '/stats');

        $response->assertStatus(200);

        $result = json_decode($response->getContent(), true);

        $this->assertLessThanOrEqual(10, count($result['data']['acessados_ultimos_dias']), 'Deve retornar 10 dias');

        $ultimoDia = now()->sub(10, 'days')->format('Y-m-d');

        foreach ($result['data']['acessados_ultimos_dias'] as $acesso) {
            $this->assertGreaterThanOrEqual($ultimoDia, $acesso['date'], 'Deve retornar os acessos dos últimos 10 dias');
        }
    }

    /**
     * @test
     */
    public function test_estatisticas_somente_os_ultimos_10_dias()
    {
        $redirect = Redirect::factory()->create();

        $redirect->code = (new Hashids(config('hashid.key'), config('hashid.length')))->encode($redirect->id);

        $redirect->save();

        $data = now();

        for ($i = 0; $i < 20; $i++) {
            $redirectLog = RedirectLog::factory()
                ->create(
                    [
                        'redirect_id' => $redirect->id,
                        'ip_request' => '192.168.0.0',
                        'user_agent_request' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36',
                        'header_referer_request' => 'https://www.google.com',
                        'query_param_request' => json_encode([
                            'utm_source' => 'facebook',
                            'utm_medium' => 'cpc',
                            'utm_campaign' => 'black_friday',
                            'utm_content' => 'link1',
                            'utm_term' => 'term1'
                        ]),
                        'access_at' => $data,
                        'created_at' => $data,
                        'updated_at' => $data,
                    ],
                );
            $redirectLog->save();

            $data->subDays(1);
        }

        $response = $this->getJson('/api/redirect/' . $redirect->code . '/stats');

        $response->assertStatus(200);

        $result = json_decode($response->getContent(), true);

        $this->assertLessThanOrEqual(10, count($result['data']['acessados_ultimos_dias']), 'Deve retornar 10 dias');
    }
}
