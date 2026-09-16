<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('gateway', 32)->nullable()->after('payment_method');
            $table->string('gateway_ref')->nullable()->after('gateway');
            $table->text('gateway_checkout_url')->nullable()->after('gateway_ref');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['gateway', 'gateway_ref', 'gateway_checkout_url']);
        });
    }
};
