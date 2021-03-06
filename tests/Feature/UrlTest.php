<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\UrlController;

class UrlTest extends TestCase
{
    use RefreshDatabase;

    public function testHome(): void
    {
        $response = $this->get(route('home'));
        $response->assertOk();
    }
    public function testShow(): void
    {
        $urlInsertedId = DB::table(UrlController::getTableName())->insertGetId([
            'name' => Factory::create()->url(),
        ]);
        $response = $this->get(route('urls.show', ['url' => $urlInsertedId]));
        $response->assertOk();
    }
    public function testIndex(): void
    {
        $data = [];
        for ($i = 0; $i < 10; $i++) {
            $data[] = ['name' => Factory::create()->url()];
            $data[] = ['name' => Factory::create()->url()];
        }
        DB::table(UrlController::getTableName())->insert($data);
        $response = $this->get(route('urls.index'));
        $response->assertOk();
        foreach ($data as $url) {
            $response->assertSeeInOrder($url);
        }
    }

    public function testStore(): void
    {
        $data = ['url' => ['name' => Factory::create()->url()]];
        $response = $this->post(route('urls.store'), $data);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas(UrlController::getTableName(), $data['url']);
    }

    public function testDestroy(): void
    {
        $urlInsertedId = DB::table(UrlController::getTableName())->insertGetId([
            'name' => Factory::create()->url(),
        ]);
        $response = $this->delete(route('urls.destroy', [$urlInsertedId]));
        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseMissing(UrlController::getTableName(), ['id' => $urlInsertedId]);
    }
}
