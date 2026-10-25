<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accommodations', function (Blueprint $table) {
            $table->index('accommodation_type_id', 'accommodations_type_id_index');
        });

        Schema::table('accommodations', function (Blueprint $table) {
            $table->unique('number');
        });

        Schema::table('accommodations', function (Blueprint $table) {
            $table->dropUnique(['accommodation_type_id', 'number']);
        });
    }

    public function down(): void
    {
        Schema::table('accommodations', function (Blueprint $table) {
            $table->unique(['accommodation_type_id', 'number']);
        });

        Schema::table('accommodations', function (Blueprint $table) {
            $table->dropUnique(['number']);
        });

        Schema::table('accommodations', function (Blueprint $table) {
            $table->dropIndex('accommodations_type_id_index');
        });
    }
};
