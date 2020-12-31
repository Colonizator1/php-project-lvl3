<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Faker\Factory;
use Illuminate\Support\Facades\DB;

class DomainsCheckTest extends TestCase
{
    public function testStore()
    {
        $domainInsertedId = DB::table('domains')->insertGetId([
            'name' => Factory::create()->domainName(),
        ]);
        $response = $this->post(route('domains_check.store', $domainInsertedId));
        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('domain_checks', ['domain_id' => $domainInsertedId]);
    }
}
