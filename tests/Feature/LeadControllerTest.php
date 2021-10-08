<?php

namespace Tests\Feature;

use App\Models\{Lead, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LeadControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use LazilyRefreshDatabase;

    /** @test */
    public function itListAllLeadsWithPagination()
    {
        Lead::factory(30)->create();
        $response = $this->get('/api/leads');

        // dd($response->json());
        $response->assertOk()
            ->assertJsonStructure(['data', 'meta', 'links'])
            ->assertJsonCount(20, 'data')
            ->assertJsonStructure(['data' => ['*' => ['id', 'title', 'description', 'contact_person']]]);
    }

    /** @test */
    public function itReturnAllLeadsOfACreator()
    {
        $user = User::factory()->create();
        $user1 = User::factory()->create();
        Lead::factory(3)->create([
            'creator' => $user->id
        ]);

        Lead::factory(4)->create([
            'creator' => $user1->id
        ]);

        // $this->actingAs($user);
        // Sanctum::actingAs($user, []);

        $response = $this->get('/api/leads?creator='.$user->id);

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }
}
