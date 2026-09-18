<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('gateway')->nullable()->after('payment_method');
            $table->string('gateway_source_id')->nullable()->unique()->after('gateway');
            $table->string('gateway_payment_id')->nullable()->after('gateway_source_id');
            $table->string('gateway_status')->nullable()->after('gateway_payment_id');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['gateway', 'gateway_source_id', 'gateway_payment_id', 'gateway_status']);
        });
    }
};
