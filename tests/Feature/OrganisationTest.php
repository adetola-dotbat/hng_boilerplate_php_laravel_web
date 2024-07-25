<?php

namespace Tests\Unit;

use App\Models\ActivityLog;
use App\Models\Organisation;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrganisationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_create_organisation()
    {

        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson('/api/v1/organisations', [
            // "user_id" => (string)$user->id,
            "email" => "mark.essienm@gmail.co.uk",
            "name" => "Ruxy Now Organisation",
            "description" => "With description like a big man",
            "industry" => "Money",
            "address" => "Money",
            "type" => "Money",
            "country" => "Money",
            "state" => "Money"
        ]);

        // Ensure organisaton is created successfully.
        $response->assertStatus(201);

        // Assert that organisation has the correct name and owner_id
        $this->assertDatabaseHas('organisations', [
            "name" => "Ruxy Now Organisation",
            // "user_id" => (string)$user->id
        ]);
    }

    public function test_user_activity_logs()
    {
        $user = User::factory()->create();
        $organization = Organisation::factory()->create();
        // dd($organization);

        // Create some activity logs for the user in the organization
        $activityLogs = ActivityLog::factory()->count(3)->create([
            'user_id' => $user->id,
            'organisation_id' => $organization->org_id,
        ]);

        $this->actingAs($user);

        $response = $this->getJson("/api/v1/organizations/{$organization->org_id}/users/{$user->id}/activity-logs");

        // Ensure the response status is 200 (OK)
        $response->assertStatus(200);

        // Ensure the response contains the correct data
        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                '*' => [
                    'id',
                    'action',
                    'user_id',
                    'organisation_id',
                    'created_at',
                    'updated_at',
                ]
            ]
        ]);
    }
}
