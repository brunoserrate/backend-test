<?php

namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\Redirect;

class RedirectApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_criar_url_valida(){
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

    public function test_dns_invalido() {
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
    public function test_url_invalida() {
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
    public function test_url_igual_a_urL_da_aplicacao() {
        $response = $this->postJson('/api/redirect', [
            'redirect_url' => env('APP_URL') . ":" . env('APP_PORT')
        ]);

        $response->assertStatus(422);

        $result = json_decode($response->getContent(), true);

        $this->assertNotEmpty($result['message'], 'Retorno deve conter uma mensagem de erro');
    }

    /**
     * @test
     */
    public function test_url_sem_https() {
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
    public function test_url_diferente_de_200_ou_201() {
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
    public function test_url_com_query_params_com_chave_vazia() {
        $response = $this->postJson('/api/redirect', [
            'redirect_url' => 'https://www.google.com?param='
        ]);

        $response->assertStatus(422);

        $result = json_decode($response->getContent(), true);

        $this->assertNotEmpty($result['message'], 'Retorno deve conter uma mensagem de erro');
    }

}