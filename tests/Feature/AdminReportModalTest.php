<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\IncidentReport;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReportModalTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $slug): User
    {
        $role = Role::query()->firstOrCreate(['slug' => $slug], ['name' => ucfirst($slug)]);

        return User::query()->create([
            'role_id' => $role->id,
            'name' => ucfirst($slug).' User',
            'email' => $slug.'@example.com',
            'password' => 'StrongPass123',
            'is_active' => true,
        ]);
    }

    private function report(): IncidentReport
    {
        $guest = Guest::query()->create([
            'user_id' => $this->userWithRole('guest')->id,
            'contact_number' => '09171234567',
        ]);

        return IncidentReport::query()->create([
            'report_number' => 'IR-TEST-001',
            'guest_id' => $guest->id,
            'report_type' => 'broken_amenity',
            'title' => 'Leaking bathroom faucet',
            'description' => 'The sink is leaking.',
            'status' => 'pending',
            'photo' => 'reports/guest-photo.jpg',
        ]);
    }

    public function test_report_list_opens_details_in_a_modal(): void
    {
        $admin = $this->userWithRole('admin');
        $report = $this->report();

        $this->actingAs($admin)
            ->get(route('admin.reports.index'))
            ->assertOk()
            ->assertSee('data-bs-target="#rpAdminReportModal'.$report->id.'"', false)
            ->assertSee('id="rpAdminReportModal'.$report->id.'"', false)
            ->assertSee('Leaking bathroom faucet')
            ->assertSee('id="rpAdminGuestPhotoModal'.$report->id.'"', false);
    }

    public function test_report_links_open_the_modal_even_when_the_report_is_filtered_out(): void
    {
        $admin = $this->userWithRole('admin');
        $report = $this->report();

        $this->actingAs($admin)
            ->get(route('admin.reports.show', $report))
            ->assertRedirect(route('admin.reports.index', ['report' => $report->id]));

        $this->actingAs($admin)
            ->get(route('admin.reports.index', ['status' => 'resolved', 'report' => $report->id]))
            ->assertOk()
            ->assertSee('id="rpAdminReportModal'.$report->id.'"', false)
            ->assertSee('Leaking bathroom faucet');
    }
}
