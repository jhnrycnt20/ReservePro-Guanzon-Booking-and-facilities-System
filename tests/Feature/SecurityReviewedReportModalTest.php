<?php

namespace Tests\Feature;

use App\Models\IncidentReport;
use App\Models\Guest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityReviewedReportModalTest extends TestCase
{
    use RefreshDatabase;

    public function test_reviewed_report_modal_hides_the_completed_review_panel(): void
    {
        $role = Role::query()->create(['name' => 'Security', 'slug' => 'security']);
        $user = User::factory()->create([
            'role_id' => $role->id,
            'is_active' => true,
        ]);
        $guestRole = Role::query()->create(['name' => 'Guest', 'slug' => 'guest']);
        $guestUser = User::factory()->create([
            'role_id' => $guestRole->id,
            'is_active' => true,
        ]);
        $guest = Guest::query()->create([
            'user_id' => $guestUser->id,
            'contact_number' => '09171234567',
        ]);
        $report = IncidentReport::query()->create([
            'report_number' => 'IR-REVIEWED-001',
            'guest_id' => $guest->id,
            'report_type' => 'incident',
            'title' => 'Noise complaint',
            'description' => 'Reported noise near the cottages.',
            'status' => 'verified',
        ]);

        $response = $this->actingAs($user)
            ->get(route('security.incidents.index', ['report' => $report->id]));

        $response
            ->assertOk()
            ->assertSee('bi-file-text', false)
            ->assertSee('rp-report-detail-grid--read-only', false)
            ->assertDontSee('rp-report-review-card', false)
            ->assertDontSee('Investigation completed');
    }
}
