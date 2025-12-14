<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_view_others_companies()
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $companyA = Company::factory()->create(['user_id' => $userA->id]);
        $companyB = Company::factory()->create(['user_id' => $userB->id]);

        $this->actingAs($userA);

        // Can see own company
        $response = $this->get(route('companies.show', $companyA));
        $response->assertStatus(200);

        // Cannot see other's company
        $response = $this->get(route('companies.show', $companyB));
        $response->assertStatus(403);
    }

    public function test_index_only_shows_own_companies()
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $companyA = Company::factory()->create(['user_id' => $userA->id, 'razao_social' => 'Company A']);
        $companyB = Company::factory()->create(['user_id' => $userB->id, 'razao_social' => 'Company B']);

        $this->actingAs($userA);

        $response = $this->get(route('companies.index'));
        $response->assertStatus(200);
        $response->assertSee('Company A');
        $response->assertDontSee('Company B');
    }
}
