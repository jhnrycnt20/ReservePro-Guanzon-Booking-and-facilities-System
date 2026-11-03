<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE bookings MODIFY status ENUM(
                'pending',
                'approved',
                'rejected',
                'cancelled',
                'checked_in',
                'checked_out',
                'no_show'
            ) NOT NULL DEFAULT 'pending'");
        }
        // sqlite/pgsql store status as string — new value is accepted without DDL.
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::table('bookings')->where('status', 'no_show')->update(['status' => 'cancelled']);

            DB::statement("ALTER TABLE bookings MODIFY status ENUM(
                'pending',
                'approved',
                'rejected',
                'cancelled',
                'checked_in',
                'checked_out'
            ) NOT NULL DEFAULT 'pending'");
        }
    }
};
