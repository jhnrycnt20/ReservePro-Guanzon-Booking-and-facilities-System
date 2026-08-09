<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incident_reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_number')->unique();
            $table->foreignId('guest_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('report_type', ['incident', 'broken_amenity', 'complaint', 'maintenance']);
            $table->string('title');
            $table->text('description');
            $table->string('location')->nullable();
            $table->string('photo')->nullable();
            $table->enum('status', [
                'pending',
                'verified',
                'invalid',
                'in_progress',
                'resolved',
                'closed',
            ])->default('pending');
            $table->foreignId('security_guard_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('investigation_notes')->nullable();
            $table->string('investigation_photo')->nullable();
            $table->text('invalid_reason')->nullable();
            $table->foreignId('front_desk_staff_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('resolution_notes')->nullable();
            $table->string('resolution_action')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incident_reports');
    }
};
