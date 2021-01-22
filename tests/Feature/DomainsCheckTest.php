<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use App\Jobs\CheckDomainJob;
use App\Http\Controllers\DomainController;
use App\Http\Controllers\DomainCheckController;

class DomainsCheckTest extends TestCase
{
    public function testStore(): void
    {
        Queue::fake();
        $domainName = Faker::create()->domainName();
        $domainInsertedId = DB::table(DomainController::getTableName())->insertGetId([
            'name' => $domainName,
        ]);

        $response = $this->post(route('domains.checks.store', $domainInsertedId));
        Queue::assertPushed(CheckDomainJob::class, function ($job) use ($domainName) {
            return $job->domainName === $domainName;
        });
        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas(DomainCheckController::getTableName(), ['domain_id' => $domainInsertedId]);
    }
}
