<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    /*
     * A basic feature test example.
     *
     * @return void
     */
    use LazilyRefreshDatabase;

    /** @test */
    public function itCanUpdateUserProfile()
    {
        $user = User::factory()->create([
            'name' => 'ibrahim',
            'email' => 'ibrahim@gmail.com',
            'username' => 'ibrahim',
        ]);

        Sanctum::actingAs($user, ['user.update']);

        $response = $this->putJson('/api/v1/user/'.$user->id, [
            'name' => 'adedayo',
            'username' => 'tulbadex',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'adedayo')
            ->assertJsonPath('data.username', 'tulbadex');

        $this->assertDatabaseHas('users', [
            'id' => $response->json('data.id'),
            'name' => $response->json('data.name'),
            'username' => $response->json('data.username'),
        ]);
    }

    /** @test */
    public function itAllowAdminToUpdateUserProfile()
    {
        $admin = User::factory()->create([
            'name' => 'rash',
            'email' => 'rash@gmail.com',
            'username' => 'rashade',
            'is_admin' => true,
        ]);

        $user = User::factory()->create([
            'name' => 'ade',
            'email' => 'ade@gmail.com',
            'username' => 'adetayo',
        ]);

        Sanctum::actingAs($admin, ['user.update']);

        $response = $this->putJson('/api/v1/user/'.$user->id, [
            'name' => 'adettayo',
            'username' => 'tumrise',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'adettayo')
            ->assertJsonPath('data.username', 'tumrise');
            
        $this->assertDatabaseHas('users', [
            'id' => $response->json('data.id'),
            'name' => $response->json('data.name'),
            'username' => $response->json('data.username'),
        ]);
    }

    /** @test */
    public function itDoesNotAllowUserToDeleteAccount()
    {

        $user = User::factory()->create([
            'name' => 'samdede',
            'email' => 'samdede@gmail.com',
            'username' => 'samdedearise',
        ]);

        Sanctum::actingAs($user, ['user.delete']);

        $response = $this->delete('/api/v1/user/'.$user->id);

        $response
            ->assertStatus(403)
            ->assertForbidden();
        $this->assertModelExists($user);
    }

    /** @test */
    public function itAllowAdminToDeleteUser()
    {
        $admin = User::factory()->create([
            'name' => 'amend',
            'email' => 'amend@gmail.com',
            'username' => 'amendrash',
            'is_admin' => true,
        ]);

        $user = User::factory()->create([
            'name' => 'rickmesh',
            'email' => 'rickmesh@gmail.com',
            'username' => 'rickmeshade',
        ]);

        Sanctum::actingAs($admin, ['user.delete']);

        $response = $this->delete('/api/v1/user/'.$user->id);

        $response->assertOk();

        $this->assertDeleted($user);
        $this->assertModelMissing($user);
    }
}
