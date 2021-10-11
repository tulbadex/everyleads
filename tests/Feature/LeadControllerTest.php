<?php

namespace Tests\Feature;

use App\Models\{Lead, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
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

    /** @test */
    public function itReturnAllLeadsAssign()
    {
        $user = User::factory()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        Lead::factory()->create([
            'creator' => $user->id,
            'assign_to' => $user1->id,
        ]);

        Lead::factory()->create([
            'creator' => $user2->id,
            'assign_to' => $user1->id,
        ]);

        Lead::factory(6)->create();

        $response = $this->get('/api/leads?assign='.$user1->id);

        // dd($response);

        $response->assertOk()
            ->assertJsonStructure(['data', 'meta', 'links'])
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.assign.id', $user1->id);
    }

     /** @test */
     public function itReturnAllLeadsByStatus()
     {
         $user = User::factory()->create();
         $user1 = User::factory()->create();
         $user2 = User::factory()->create();
         Lead::factory(5)->create([
             'creator' => $user->id,
             'status' => Lead::STATUS_FOLLOW_UP
         ]);

         Lead::factory(3)->create([
             'creator' => $user->id,
             'status' => Lead::STATUS_LOST
         ]);

         Lead::factory(6)->create();

         $response = $this->get('/api/leads?creator='.$user->id."&status=".Lead::STATUS_LOST);

        //  dd($response);

         $response->assertOk()
             ->assertJsonStructure(['data', 'meta', 'links'])
             ->assertJsonCount(3, 'data')
             ->assertJsonPath('data.0.creator.id', $user->id);
     }

     /** @test */
     public function itOrderLeadsByIdDesc()
     {
        User::factory()->create();

        Lead::factory()->create([
            'title' => "First Title",
            'status' => Lead::STATUS_FOLLOW_UP
        ]);

        Lead::factory()->create([
            'title' => "Second Title",
            'status' => Lead::STATUS_LOST
        ]);

        $response = $this->get('/api/leads');
        // dd($response);

        $this->assertEquals('Second Title', $response->json('data')[0]['title']);
        $this->assertEquals('First Title', $response->json('data')[1]['title']);
     }

     /** @test */
     public function itCreateALead()
     {
        $user = User::factory()->create();
        $user1 = User::factory()->create();

        $endDate = now()->addDay(1)->toDateString();
        $toDate = now()->addDay(15)->toDateString();

        Sanctum::actingAs($user, ['leads.create']);

        $response = $this->postJson('/api/leads', [
            'title' => 'This is title',
            'description' => 'This is description',
            'value' => 99,
            'source' => "Facebook",
            'contact_person' => "Raheem",
            'contact_email' => "raheem@gmail.com",
            'contact_phone' => "09034234568",
            'contact_organization' => "Wahik and co",
            'start_date' => now()->addDays(1)->toDateString(),
            'end_date' => $toDate,
            // 'status' => Lead::STATUS_FOLLOW_UP,
            'creator' => $user->id,
            'assign_to' => $user1->id,
        ]);

        // dd($response);

        $response->assertCreated()
            ->assertJsonPath('data.status', Lead::STATUS_NEGOTIATION)
            ->assertJsonPath('data.source', "Facebook")
            ->assertJsonPath('data.creator.id', $user->id)
            ->assertJsonPath('data.assign.id', $user1->id);

        $this->assertDatabaseHas('leads', [
            'id' => $response->json('data.id')
        ]);
     }

    /** @test */
    public function itDoesntAllowLeadCreateIfScopeNotProvided()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, []);

        $response = $this->postJson('/api/leads');

        $response->assertStatus(403);

    }

    /** @test */
    public function itAllowsCreatingIfScopeIsProvided()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['leads.create']);

        $response = $this->postJson('/api/leads');

        $this->assertNotEquals(Response::HTTP_FORBIDDEN, $response->status());

    }

    /** @test */
    public function itUpdatesALead()
    {
       $user = User::factory()->create([
           'name' => 'Halilu Tahir'
       ]);

       $lead = Lead::factory()->create([
           'creator' => $user->id
       ]);

        Sanctum::actingAs($user, ['leads.update']);

       $response = $this->putJson('/api/leads/'.$lead->id, [
           'title' => 'Updated leads title'
       ]);

       // dd($response->json());

       $response->assertOk()
           ->assertJsonPath('data.creator.id', $user->id)
           ->assertJsonPath('data.title', 'Updated leads title');
        $this->assertAuthenticated();

    }

    /** @test */
    public function itCannotUpdateAnLeadThatIsForAnotherUser()
    {
       $user = User::factory()->create();
       $another_user = User::factory()->create();
       $lead = Lead::factory()->create([
           'creator' => $another_user->id
       ]);

       $this->actingAs($user);

       $response = $this->putJson('/api/leads/'.$lead->id, [
           'title' => 'Can not update title for other user'
       ]);

       // dd($response->json());
       // dd($response->status());

       $response->assertStatus(403);
       $response->assertForbidden();

    }

    /** @test  */
    public function itCanDeleteLeads()
    {

       $user = User::factory()->create();
       $lead = Lead::factory()->create([
           'creator' => $user->id
       ]);

       Sanctum::actingAs($user, ['leads.delete']);

       $response = $this->delete('/api/leads/'.$lead->id);
       $response->assertOk();

       $this->assertDeleted($lead);
    }

    /** @test  */
    public function itCannotDeleteLeadThatBelongsToAnotherUser()
    {

       $user = User::factory()->create();
       $user1 = User::factory()->create();
       $lead = Lead::factory()->create([
           'creator' => $user1->id
       ]);

       Sanctum::actingAs($user, ['leads.delete']);

       $response = $this->delete('/api/leads/'.$lead->id);

       $response->assertStatus(403);
       $response->assertForbidden();
       $this->assertModelExists($lead);
    }

}
