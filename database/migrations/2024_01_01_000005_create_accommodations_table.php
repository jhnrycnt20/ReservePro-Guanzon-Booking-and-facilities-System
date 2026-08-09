<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accommodations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('accommodation_type_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('number')->comment('Room/unit code');
            $table->text('description')->nullable();
            $table->unsignedInteger('capacity')->default(2);
            $table->decimal('rate', 12, 2);
            $table->enum('status', ['available', 'reserved', 'occupied', 'maintenance', 'inactive'])->default('available');
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['accommodation_type_id', 'number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accommodations');
    }
};
