<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('feedback', 'scope')) {
            Schema::table('feedback', function (Blueprint $table) {
                $table->string('scope', 32)->default('stay')->after('guest_id');
            });
        }

        if (! Schema::hasColumn('feedback', 'accommodation_id')) {
            Schema::table('feedback', function (Blueprint $table) {
                $table->foreignId('accommodation_id')->nullable()->after('scope')->constrained()->nullOnDelete();
            });
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            Schema::table('feedback', function (Blueprint $table) {
                $table->dropForeign(['booking_id']);
                $table->dropUnique(['booking_id', 'guest_id']);
            });

            DB::statement('ALTER TABLE feedback MODIFY booking_id BIGINT UNSIGNED NULL');

            Schema::table('feedback', function (Blueprint $table) {
                $table->foreign('booking_id')->references('id')->on('bookings')->nullOnDelete();
                $table->unique(['booking_id', 'guest_id']);
            });

            return;
        }

        if ($driver === 'sqlite') {
            Schema::disableForeignKeyConstraints();

            Schema::create('feedback_tmp_general', function (Blueprint $table) {
                $table->id();
                $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('guest_id')->constrained()->cascadeOnDelete();
                $table->string('scope', 32)->default('stay');
                $table->foreignId('accommodation_id')->nullable()->constrained()->nullOnDelete();
                $table->unsignedTinyInteger('rating');
                $table->text('comment')->nullable();
                $table->timestamps();
                $table->unique(['booking_id', 'guest_id']);
            });

            $rows = DB::table('feedback')->get();
            foreach ($rows as $row) {
                DB::table('feedback_tmp_general')->insert([
                    'id' => $row->id,
                    'booking_id' => $row->booking_id,
                    'guest_id' => $row->guest_id,
                    'scope' => $row->scope ?? 'stay',
                    'accommodation_id' => $row->accommodation_id ?? null,
                    'rating' => $row->rating,
                    'comment' => $row->comment,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);
            }

            Schema::drop('feedback');
            Schema::rename('feedback_tmp_general', 'feedback');
            Schema::enableForeignKeyConstraints();
        }
    }

    public function down(): void
    {
        // Non-destructive rollback is intentionally omitted for SQLite rebuilds.
        if (Schema::hasColumn('feedback', 'accommodation_id')) {
            Schema::table('feedback', function (Blueprint $table) {
                $table->dropConstrainedForeignId('accommodation_id');
            });
        }

        if (Schema::hasColumn('feedback', 'scope')) {
            Schema::table('feedback', function (Blueprint $table) {
                $table->dropColumn('scope');
            });
        }
    }
};
