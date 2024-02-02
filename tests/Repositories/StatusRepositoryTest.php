<?php

namespace Tests\Repositories;

use App\Models\status;
use App\Repositories\statusRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;

class statusRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    protected statusRepository $statusRepo;

    public function setUp() : void
    {
        parent::setUp();
        $this->statusRepo = app(statusRepository::class);
    }

    /**
     * @test create
     */
    public function test_create_status()
    {
        $status = status::factory()->make()->toArray();

        $createdstatus = $this->statusRepo->create($status);

        $createdstatus = $createdstatus->toArray();
        $this->assertArrayHasKey('id', $createdstatus);
        $this->assertNotNull($createdstatus['id'], 'Created status must have id specified');
        $this->assertNotNull(status::find($createdstatus['id']), 'status with given id must be in DB');
        $this->assertModelData($status, $createdstatus);
    }

    /**
     * @test read
     */
    public function test_read_status()
    {
        $status = status::factory()->create();

        $dbstatus = $this->statusRepo->find($status->id);

        $dbstatus = $dbstatus->toArray();
        $this->assertModelData($status->toArray(), $dbstatus);
    }

    /**
     * @test update
     */
    public function test_update_status()
    {
        $status = status::factory()->create();
        $fakestatus = status::factory()->make()->toArray();

        $updatedstatus = $this->statusRepo->update($fakestatus, $status->id);

        $this->assertModelData($fakestatus, $updatedstatus->toArray());
        $dbstatus = $this->statusRepo->find($status->id);
        $this->assertModelData($fakestatus, $dbstatus->toArray());
    }

    /**
     * @test delete
     */
    public function test_delete_status()
    {
        $status = status::factory()->create();

        $resp = $this->statusRepo->delete($status->id);

        $this->assertTrue($resp);
        $this->assertNull(status::find($status->id), 'status should not exist in DB');
    }
}
