<?php

namespace Tests\Feature;

use App\Models\Feedback;
use App\Models\Guest;
use App\Models\IncidentReport;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeedbackAndReportPaginationTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $role = Role::query()->create(['name' => 'Administrator', 'slug' => 'admin']);

        return User::query()->create([
            'role_id' => $role->id,
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'StrongPass123',
            'is_active' => true,
        ]);
    }

    public function test_feedback_index_shows_ten_rows_per_page(): void
    {
        $admin = $this->admin();

        foreach (range(1, 11) as $number) {
            Feedback::query()->create([
                'rating' => 5,
                'comment' => "Feedback {$number}",
            ]);
        }

        $this->actingAs($admin)
            ->get(route('admin.feedback.index'))
            ->assertOk()
            ->assertViewHas('feedback', fn ($feedback) => $feedback->perPage() === 10
                && $feedback->count() === 10
                && $feedback->total() === 11);
    }

    public function test_reports_index_shows_ten_rows_per_page(): void
    {
        $admin = $this->admin();
        $guestRole = Role::query()->create(['name' => 'Guest', 'slug' => 'guest']);
        $guestUser = User::query()->create([
            'role_id' => $guestRole->id,
            'name' => 'Guest User',
            'email' => 'guest@example.com',
            'password' => 'StrongPass123',
            'is_active' => true,
        ]);
        $guest = Guest::query()->create([
            'user_id' => $guestUser->id,
            'contact_number' => '09171234567',
        ]);

        foreach (range(1, 11) as $number) {
            IncidentReport::query()->create([
                'report_number' => "IR-TEST-{$number}",
                'guest_id' => $guest->id,
                'report_type' => 'incident',
                'title' => "Report {$number}",
                'description' => "Incident report {$number}",
                'status' => 'pending',
            ]);
        }

        $this->actingAs($admin)
            ->get(route('admin.reports.index'))
            ->assertOk()
            ->assertViewHas('reports', fn ($reports) => $reports->perPage() === 10
                && $reports->count() === 10
                && $reports->total() === 11);
    }
}
