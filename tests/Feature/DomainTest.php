<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Faker\Factory;
use Illuminate\Support\Facades\DB;

class DomainTest extends TestCase
{
    use RefreshDatabase;

    public function testHome()
    {
        $response = $this->get(route('home'));
        $response->assertOk();
    }
    public function testShow()
    {
        $domainInsertedId = DB::table('domains')->insertGetId([
            'name' => Factory::create()->url(),
        ]);
        $response = $this->get(route('domains.show', ['id' => $domainInsertedId]));
        $response->assertOk();
    }
    public function testIndex()
    {
        $data = [];
        for ($i = 0; $i < 10; $i++) {
            $data[] = ['name' => Factory::create()->url()];
            $data[] = ['name' => Factory::create()->domainName()];
        }
        DB::table('domains')->insert($data);
        $response = $this->get(route('domains.index'));
        $response->assertOk();
        foreach ($data as $domain) {
            $response->assertSeeInOrder($domain);
        }
    }

    public function testStore()
    {
        $data = ['domain' => ['name' => Factory::create()->url()]];
        $response = $this->post(route('domains.store'), $data);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('domains', $data['domain']);
    }

    public function testDestroy()
    {
        $domainInsertedId = DB::table('domains')->insertGetId([
            'name' => Factory::create()->url(),
        ]);
        $response = $this->delete(route('domains.destroy', [$domainInsertedId]));
        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseMissing('domains', ['id' => $domainInsertedId]);
    }
}
