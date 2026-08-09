<?php

namespace App\Services;

use App\Enums\IncidentStatus;
use App\Models\IncidentReport;
use App\Models\User;
use App\Notifications\IncidentClosedNotification;
use App\Notifications\IncidentInProgressNotification;
use App\Notifications\IncidentInvalidNotification;
use App\Notifications\IncidentResolvedNotification;
use App\Notifications\IncidentSubmittedNotification;
use App\Notifications\IncidentVerifiedNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class IncidentReportService
{
    public function __construct(
        protected AuditService $auditService,
        protected NotificationService $notificationService,
    ) {
    }

    public function generateReportNumber(): string
    {
        do {
            $number = 'IR-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));
        } while (IncidentReport::withTrashed()->where('report_number', $number)->exists());

        return $number;
    }

    public function storeUploadedImage(?UploadedFile $file, string $directory = 'incidents'): ?string
    {
        if (! $file) {
            return null;
        }

        $filename = Str::uuid()->toString().'.'.$file->getClientOriginalExtension();

        return $file->storeAs($directory, $filename, 'public');
    }

    public function submit(array $data, User $guestUser, ?UploadedFile $photo = null): IncidentReport
    {
        return DB::transaction(function () use ($data, $guestUser, $photo) {
            $guest = $guestUser->guest;

            if (! $guest) {
                throw ValidationException::withMessages([
                    'guest' => 'Guest profile is required to submit a report.',
                ]);
            }

            $report = IncidentReport::query()->create([
                'report_number' => $this->generateReportNumber(),
                'guest_id' => $guest->id,
                'booking_id' => $data['booking_id'] ?? null,
                'report_type' => $data['report_type'],
                'title' => $data['title'],
                'description' => $data['description'],
                'location' => $data['location'],
                'photo' => $this->storeUploadedImage($photo),
                'status' => IncidentStatus::Pending,
            ]);

            $this->auditService->log('incident.submitted', $report, null, $report->toArray(), $guestUser);
            $this->notificationService->notify($guestUser, new IncidentSubmittedNotification($report));

            return $report->fresh();
        });
    }

    public function verify(IncidentReport $report, User $guard, array $data, ?UploadedFile $photo = null): IncidentReport
    {
        $this->assertStatus($report, IncidentStatus::Pending);

        return DB::transaction(function () use ($report, $guard, $data, $photo) {
            $old = $report->toArray();

            $report->update([
                'status' => IncidentStatus::Verified,
                'security_guard_id' => $guard->id,
                'investigation_notes' => $data['investigation_notes'],
                'investigation_photo' => $this->storeUploadedImage($photo, 'investigations') ?? $report->investigation_photo,
            ]);

            $this->auditService->log('incident.verified', $report, $old, $report->fresh()->toArray(), $guard);

            if ($report->guest?->user) {
                $this->notificationService->notify($report->guest->user, new IncidentVerifiedNotification($report));
            }

            User::query()->whereHas('role', fn ($q) => $q->where('slug', 'front_desk'))
                ->where('is_active', true)
                ->get()
                ->each(fn (User $staff) => $this->notificationService->notify($staff, new IncidentVerifiedNotification($report)));

            return $report->fresh();
        });
    }

    public function markInvalid(IncidentReport $report, User $guard, string $reason): IncidentReport
    {
        $this->assertStatus($report, IncidentStatus::Pending);

        return DB::transaction(function () use ($report, $guard, $reason) {
            $old = $report->toArray();

            $report->update([
                'status' => IncidentStatus::Invalid,
                'security_guard_id' => $guard->id,
                'invalid_reason' => $reason,
                'closed_at' => now(),
            ]);

            $this->auditService->log('incident.invalid', $report, $old, $report->fresh()->toArray(), $guard);

            if ($report->guest?->user) {
                $this->notificationService->notify($report->guest->user, new IncidentInvalidNotification($report));
            }

            return $report->fresh();
        });
    }

    public function startProgress(IncidentReport $report, User $staff, array $data): IncidentReport
    {
        $this->assertStatus($report, IncidentStatus::Verified);

        return DB::transaction(function () use ($report, $staff, $data) {
            $old = $report->toArray();

            $report->update([
                'status' => IncidentStatus::InProgress,
                'front_desk_staff_id' => $staff->id,
                'resolution_action' => $data['resolution_action'] ?? null,
                'resolution_notes' => $data['resolution_notes'] ?? null,
            ]);

            $this->auditService->log('incident.in_progress', $report, $old, $report->fresh()->toArray(), $staff);

            if ($report->guest?->user) {
                $this->notificationService->notify($report->guest->user, new IncidentInProgressNotification($report));
            }

            return $report->fresh();
        });
    }

    public function resolve(IncidentReport $report, User $staff, string $notes): IncidentReport
    {
        $this->assertStatus($report, IncidentStatus::InProgress);

        return DB::transaction(function () use ($report, $staff, $notes) {
            $old = $report->toArray();

            $report->update([
                'status' => IncidentStatus::Resolved,
                'front_desk_staff_id' => $staff->id,
                'resolution_notes' => $notes,
                'resolved_at' => now(),
            ]);

            $this->auditService->log('incident.resolved', $report, $old, $report->fresh()->toArray(), $staff);

            if ($report->guest?->user) {
                $this->notificationService->notify($report->guest->user, new IncidentResolvedNotification($report));
            }

            return $report->fresh();
        });
    }

    public function close(IncidentReport $report, User $actor): IncidentReport
    {
        $this->assertStatus($report, IncidentStatus::Resolved);

        return DB::transaction(function () use ($report, $actor) {
            $old = $report->toArray();

            $report->update([
                'status' => IncidentStatus::Closed,
                'closed_at' => now(),
            ]);

            $this->auditService->log('incident.closed', $report, $old, $report->fresh()->toArray(), $actor);

            if ($report->guest?->user) {
                $this->notificationService->notify($report->guest->user, new IncidentClosedNotification($report));
            }

            return $report->fresh();
        });
    }

    protected function assertStatus(IncidentReport $report, IncidentStatus $expected): void
    {
        if ($report->status !== $expected) {
            throw ValidationException::withMessages([
                'status' => "Report must be {$expected->value} for this action.",
            ]);
        }
    }
}
