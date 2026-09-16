<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promos', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();
            $table->string('name')->nullable();
            $table->decimal('discount_percent', 5, 2);
            $table->boolean('applies_to_all')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('accommodation_promo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promo_id')->constrained()->cascadeOnDelete();
            $table->foreignId('accommodation_id')->constrained()->cascadeOnDelete();
            $table->unique(['promo_id', 'accommodation_id']);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('promo_id')->nullable()->after('created_by')->constrained('promos')->nullOnDelete();
            $table->string('promo_code', 32)->nullable()->after('promo_id');
            $table->decimal('original_amount', 12, 2)->nullable()->after('promo_code');
            $table->decimal('discount_amount', 12, 2)->default(0)->after('original_amount');
            $table->decimal('discount_percent', 5, 2)->nullable()->after('discount_amount');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('promo_id');
            $table->dropColumn(['promo_code', 'original_amount', 'discount_amount', 'discount_percent']);
        });

        Schema::dropIfExists('accommodation_promo');
        Schema::dropIfExists('promos');
    }
};
