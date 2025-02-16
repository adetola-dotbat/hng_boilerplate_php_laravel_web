<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Organisation;
use App\Models\Role;
use Tymon\JWTAuth\Facades\JWTAuth;

class OrganisationRemoveUserTest extends TestCase
{
    // use RefreshDatabase;
    use LazilyRefreshDatabase;

    /** @test */
    public function it_test_unauthenticated_user_cannot_remove_user()
    {
        $user = User::factory()->create();
        $response = $this->delete('/api/v1/organizations/1/users/1');
        $response->assertStatus(401)
                 ->assertJson([
                     'status' => 'Unauthorized',
                     'message' => 'User not authenticated',
                     'status_code' => 401,
                 ]);
    }

    /** @test */
    public function it_test_unauthorized_user_cannot_remove_user()
    {
        $user = User::factory()->create();
        $organization = Organisation::factory()->create();

        $anotherUser = User::factory()->create();
        $token = JWTAuth::fromUser($anotherUser);

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
                        ->deleteJson("/api/v1/organizations/{$organization->org_id}/users/{$user->id}");

        $response->assertStatus(403)
                ->assertJson([
                    'status' => 'Forbidden',
                    'message' => 'Only admin can remove users',
                    'status_code' => 403,
                ]);
    }

    public function test_superadmin_can_remove_user()
    {
        $org = Organisation::factory()->create();
        $superadmin = User::factory()->create();
        $user = User::factory()->create();

        $superadmin->roles()->create(['name' => 'superadmin', 'org_id' => $org->org_id]);
        $org->users()->attach($user);

        // Generate a JWT token for the superadmin
        $token = JWTAuth::fromUser($superadmin);

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
                         ->deleteJson("/api/v1/organizations/{$org->org_id}/users/{$user->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'success',
                     'message' => 'user deleted successfully',
                     'status_code' => 200,
                 ]);
    }

    /** @test */
    public function it_test_orgadmin_can_remove_user()
    {
        $org = Organisation::factory()->create();
        $superadmin = User::factory()->create();
        $user = User::factory()->create();

        // Set the superadmin role
        $role = Role::create(['name' => 'superadmin', 'org_id' => $org->org_id]);
        $superadmin->roles()->attach($role);

        // Add user to the organization
        $org->users()->attach($user);

        // Generate a JWT token for the superadmin
        $token = JWTAuth::fromUser($superadmin);

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
                        ->deleteJson("/api/v1/organizations/{$org->org_id}/users/{$user->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'status' => 'success',
                    'message' => 'user deleted successfully',
                    'status_code' => 200,
                ]);

        // Assert the user has been removed from the organization
        $this->assertDatabaseMissing('users_roles', [
            'user_id' => $user->id,
            'role_id' => $role->id,
        ]);
    }


    /** @test */
    public function it_test_admin_can_remove_user()
    {
        $org = Organisation::factory()->create();
        $admin = User::factory()->create();
        $user = User::factory()->create();

        // Set the admin role
        $admin->roles()->create(['name' => 'admin', 'org_id' => $org->org_id]);

        // Add user to the organization
        $org->users()->attach($user);

        // Generate a JWT token for the admin
        $token = JWTAuth::fromUser($admin);

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
                        ->deleteJson("/api/v1/organizations/{$org->org_id}/users/{$user->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'status' => 'success',
                    'message' => 'user deleted successfully',
                    'status_code' => 200,
                ]);
    }

    /** @test */
    public function it_test_remove_non_existent_user()
    {
        $orgAdmin = User::factory()->create();
        $organization = Organisation::factory()->create();

        $orgAdmin->roles()->create(['name' => 'superadmin', 'org_id' => $organization->org_id]);

        // Generate a JWT token for the admin
        $token = JWTAuth::fromUser($orgAdmin);

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
                        ->deleteJson("/api/v1/organizations/{$organization->org_id}/users/9c945f25-9870-477b-acbd-7633c9996855");
        
        $response->assertStatus(404)
                ->assertJson([
                    'status' => 'forbidden',
                    'message' => 'user not found',
                    'status_code' => 404,
                ]);
    }
    
}
