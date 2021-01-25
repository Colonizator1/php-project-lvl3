<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\DomainController;

class DomainTest extends TestCase
{
    use RefreshDatabase;

    public function testHome(): void
    {
        $response = $this->get(route('home'));
        $response->assertOk();
    }
    public function testShow(): void
    {
        $domainInsertedId = DB::table(DomainController::getTableName())->insertGetId([
            'name' => Factory::create()->domainName(),
        ]);
        $response = $this->get(route('domains.show', ['domain' => $domainInsertedId]));
        $response->assertOk();
    }
    public function testIndex(): void
    {
        $data = [];
        for ($i = 0; $i < 10; $i++) {
            $data[] = ['name' => Factory::create()->domainName()];
            $data[] = ['name' => Factory::create()->domainName()];
        }
        DB::table(DomainController::getTableName())->insert($data);
        $response = $this->get(route('domains.index'));
        $response->assertOk();
        foreach ($data as $domain) {
            $response->assertSeeInOrder($domain);
        }
    }

    public function testStore(): void
    {
        $data = ['domain' => ['name' => Factory::create()->domainName()]];
        $response = $this->post(route('domains.store'), $data);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas(DomainController::getTableName(), $data['domain']);
    }

    public function testDestroy(): void
    {
        $domainInsertedId = DB::table(DomainController::getTableName())->insertGetId([
            'name' => Factory::create()->domainName(),
        ]);
        $response = $this->delete(route('domains.destroy', [$domainInsertedId]));
        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseMissing(DomainController::getTableName(), ['id' => $domainInsertedId]);
    }
}
