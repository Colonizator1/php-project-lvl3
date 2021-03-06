<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use App\Jobs\CheckUrlJob;
use App\Http\Controllers\UrlController;
use App\Http\Controllers\UrlCheckController;

class UrlsCheckTest extends TestCase
{
    public function testStore(): void
    {
        Queue::fake();
        $urlName = Faker::create()->url;
        $urlInsertedId = DB::table(UrlController::getTableName())->insertGetId([
            'name' => $urlName,
        ]);

        $response = $this->post(route('urls.checks.store', $urlInsertedId));
        Queue::assertPushed(CheckUrlJob::class, function ($job) use ($urlName): bool {
            return $job->urlName === $urlName;
        });
        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas(UrlCheckController::getTableName(), ['url_id' => $urlInsertedId]);
    }
}
