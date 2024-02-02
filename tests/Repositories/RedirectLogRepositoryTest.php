<?php

namespace Tests\Repositories;

use App\Models\RedirectLog;
use App\Repositories\RedirectLogRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;

class RedirectLogRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    protected RedirectLogRepository $redirectLogRepo;

    public function setUp() : void
    {
        parent::setUp();
        $this->redirectLogRepo = app(RedirectLogRepository::class);
    }

    /**
     * @test create
     */
    public function test_create_redirect_log()
    {
        $redirectLog = RedirectLog::factory()->make()->toArray();

        $createdRedirectLog = $this->redirectLogRepo->create($redirectLog);

        $createdRedirectLog = $createdRedirectLog->toArray();
        $this->assertArrayHasKey('id', $createdRedirectLog);
        $this->assertNotNull($createdRedirectLog['id'], 'Created RedirectLog must have id specified');
        $this->assertNotNull(RedirectLog::find($createdRedirectLog['id']), 'RedirectLog with given id must be in DB');
        $this->assertModelData($redirectLog, $createdRedirectLog);
    }

    /**
     * @test read
     */
    public function test_read_redirect_log()
    {
        $redirectLog = RedirectLog::factory()->create();

        $dbRedirectLog = $this->redirectLogRepo->find($redirectLog->id);

        $dbRedirectLog = $dbRedirectLog->toArray();
        $this->assertModelData($redirectLog->toArray(), $dbRedirectLog);
    }

    /**
     * @test update
     */
    public function test_update_redirect_log()
    {
        $redirectLog = RedirectLog::factory()->create();
        $fakeRedirectLog = RedirectLog::factory()->make()->toArray();

        $updatedRedirectLog = $this->redirectLogRepo->update($fakeRedirectLog, $redirectLog->id);

        $this->assertModelData($fakeRedirectLog, $updatedRedirectLog->toArray());
        $dbRedirectLog = $this->redirectLogRepo->find($redirectLog->id);
        $this->assertModelData($fakeRedirectLog, $dbRedirectLog->toArray());
    }

    /**
     * @test delete
     */
    public function test_delete_redirect_log()
    {
        $redirectLog = RedirectLog::factory()->create();

        $resp = $this->redirectLogRepo->delete($redirectLog->id);

        $this->assertTrue($resp);
        $this->assertNull(RedirectLog::find($redirectLog->id), 'RedirectLog should not exist in DB');
    }
}
