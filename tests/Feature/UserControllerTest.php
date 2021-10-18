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
    public function itDoesNotAllowGuessToAccessUserList()
    {
        User::factory()->count(40)->create();

        $response = $this->get('/api/v1/user/');

        $response
            ->assertStatus(500);
        $this->assertGuest();
    }

    /** @test */
    public function itDoesNotAllowUserToFetchListOfUsers()
    {
        $user = User::factory()->create([
            'name' => 'adetunde',
            'email' => 'adetunde@gmail.com',
            'username' => 'adetunde'
        ]);

        User::factory()->count(40)->create();

        Sanctum::actingAs($user, []);

        $response = $this->get('/api/v1/user/');

        $response
            ->assertStatus(403)
            ->assertForbidden();
    }

    /** @test */
    public function itFetchesAllUsersForAdmin()
    {
        $user = User::factory()->create([
            'name' => 'ibrahim',
            'email' => 'ibrahim@gmail.com',
            'username' => 'ibrahim',
            'is_admin' => true
        ]);

        User::factory()->count(40)->create();

        Sanctum::actingAs($user, []);

        $response = $this->get('/api/v1/user/');

        // $response->dump();

        $response->assertOk()
            ->assertJsonStructure(['data', 'meta', 'links'])
            ->assertJsonCount(20, 'data')
            ->assertJsonStructure(['data' => ['*' => ['id', 'name', 'username']]]);
            // ->assertJsonFragment(['name' => 'ibrahim'])

        // $this->assertDatabaseCount('users', 41);

    }

    /** @test */
    public function itCanUpdateUserProfile()
    {
        $user = User::factory()->create([
            'name' => 'ibrahimade',
            'email' => 'ibrahimade@gmail.com',
            'username' => 'ibrahimade',
        ]);

        Sanctum::actingAs($user, ['user.update']);

        $response = $this->putJson('/api/v1/user/'.$user->id, [
            'name' => 'adedayo',
            'username' => 'tulbadex',
            'password' => 'password'
        ]);

        // dd($response);
        // $response->dump();

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
    public function itAllowAdinToCreateANewUser()
    {
        $admin = User::factory()->create([
            'name' => 'basbas',
            'email' => 'basbas@gmail.com',
            'username' => 'basbas',
            'is_admin' => true,
        ]);

        Sanctum::actingAs($admin, ['user.create']);

        $response = $this->postJson('/api/v1/user', [
            'name' => 'akeem',
            'username' => 'akeem',
            'email' => 'aladeakeem@gmail.com',
            'password' => 'password'
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'akeem')
            ->assertJsonPath('data.username', 'akeem');

        $this->assertDatabaseHas('users', [
            'id' => $response->json('data.id'),
        ]);
    }

    /** @test */
    public function itDoesNotAllowUserToCreateANewUser()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['user.create']);

        $response = $this->postJson('/api/v1/user', [
            'name' => 'akeem1',
            'username' => 'akeem1',
            'email' => 'aladeakeem1@gmail.com',
            'password' => 'password'
        ]);

        $response
            ->assertStatus(403)
            ->assertForbidden();
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
