<?php

namespace Tests\Repositories;

use App\Models\Redirect;
use App\Repositories\RedirectRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;

class RedirectRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    protected RedirectRepository $redirectRepo;

    public function setUp() : void
    {
        parent::setUp();
        $this->redirectRepo = app(RedirectRepository::class);
    }

    /**
     * @test create
     */
    public function test_create_redirect()
    {
        $redirect = Redirect::factory()->make()->toArray();

        $createdRedirect = $this->redirectRepo->create($redirect);

        $createdRedirect = $createdRedirect->toArray();
        $this->assertArrayHasKey('id', $createdRedirect);
        $this->assertNotNull($createdRedirect['id'], 'Created Redirect must have id specified');
        $this->assertNotNull(Redirect::find($createdRedirect['id']), 'Redirect with given id must be in DB');
        $this->assertModelData($redirect, $createdRedirect);
    }

    /**
     * @test read
     */
    public function test_read_redirect()
    {
        $redirect = Redirect::factory()->create();

        $dbRedirect = $this->redirectRepo->find($redirect->id);

        $dbRedirect = $dbRedirect->toArray();
        $this->assertModelData($redirect->toArray(), $dbRedirect);
    }

    /**
     * @test update
     */
    public function test_update_redirect()
    {
        $redirect = Redirect::factory()->create();
        $fakeRedirect = Redirect::factory()->make()->toArray();

        $updatedRedirect = $this->redirectRepo->update($fakeRedirect, $redirect->id);

        $this->assertModelData($fakeRedirect, $updatedRedirect->toArray());
        $dbRedirect = $this->redirectRepo->find($redirect->id);
        $this->assertModelData($fakeRedirect, $dbRedirect->toArray());
    }

    /**
     * @test delete
     */
    public function test_delete_redirect()
    {
        $redirect = Redirect::factory()->create();

        $resp = $this->redirectRepo->delete($redirect->id);

        $this->assertTrue($resp);
        $this->assertNull(Redirect::find($redirect->id), 'Redirect should not exist in DB');
    }
}
