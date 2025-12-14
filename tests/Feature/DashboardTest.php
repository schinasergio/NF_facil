<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Nfe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class DashboardTest
 * 
 * Verifies the functionality of the dashboard module.
 * 
 * @package Tests\Feature
 */
class DashboardTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test if the dashboard page is accessible.
     * 
     * @return void
     */
    public function test_can_access_dashboard()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboard');
    }

    /**
     * Test if the dashboard statistics are accurate.
     * 
     * Creates NFes with different statuses and verifies if the
     * view receives the correct aggregated counts.
     * 
     * @return void
     */
    public function test_stats_are_accurate()
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();

        // 2 Authorized
        Nfe::create(['company_id' => $company->id, 'customer_id' => Customer::factory()->create(['company_id' => $company->id])->id, 'status' => 'authorized', 'numero' => 1, 'serie' => 1, 'valor_total' => 100.00]);
        Nfe::create(['company_id' => $company->id, 'customer_id' => Customer::factory()->create(['company_id' => $company->id])->id, 'status' => 'authorized', 'numero' => 2, 'serie' => 1, 'valor_total' => 50.00]);

        // 1 Canceled
        Nfe::create(['company_id' => $company->id, 'customer_id' => Customer::factory()->create(['company_id' => $company->id])->id, 'status' => 'canceled', 'numero' => 3, 'serie' => 1, 'valor_total' => 200.00]);

        // 1 Rejected (Pending count)
        Nfe::create(['company_id' => $company->id, 'customer_id' => Customer::factory()->create(['company_id' => $company->id])->id, 'status' => 'rejected', 'numero' => 4, 'serie' => 1, 'valor_total' => 0.00]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertViewHas('authorizedCount', 2);
        $response->assertViewHas('canceledCount', 1);
        $response->assertViewHas('pendingCount', 1); // 1 rejected
        $response->assertViewHas('monthlyVolume', 150.00); // 100 + 50
    }
}
