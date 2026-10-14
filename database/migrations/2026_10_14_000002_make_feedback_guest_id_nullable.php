<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            $this->dropForeignIfExists('feedback', 'feedback_booking_id_foreign');
            $this->dropForeignIfExists('feedback', 'feedback_guest_id_foreign');

            // Unique index may still be required by a leftover FK index name — drop safely.
            try {
                Schema::table('feedback', function (Blueprint $table) {
                    $table->dropUnique(['booking_id', 'guest_id']);
                });
            } catch (\Throwable) {
                DB::statement('ALTER TABLE feedback DROP INDEX feedback_booking_id_guest_id_unique');
            }

            // Orphan index left after a prior FK drop.
            try {
                DB::statement('ALTER TABLE feedback DROP INDEX feedback_guest_id_foreign');
            } catch (\Throwable) {
                // Already gone.
            }

            DB::statement('ALTER TABLE feedback MODIFY guest_id BIGINT UNSIGNED NULL');

            Schema::table('feedback', function (Blueprint $table) {
                $table->foreign('guest_id')->references('id')->on('guests')->nullOnDelete();
                $table->foreign('booking_id')->references('id')->on('bookings')->nullOnDelete();
                $table->unique(['booking_id', 'guest_id']);
            });

            return;
        }

        if ($driver === 'sqlite') {
            Schema::disableForeignKeyConstraints();

            Schema::create('feedback_tmp_guest_nullable', function (Blueprint $table) {
                $table->id();
                $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('guest_id')->nullable()->constrained()->nullOnDelete();
                $table->string('scope', 32)->default('stay');
                $table->foreignId('accommodation_id')->nullable()->constrained()->nullOnDelete();
                $table->unsignedTinyInteger('rating');
                $table->text('comment')->nullable();
                $table->timestamps();
                $table->unique(['booking_id', 'guest_id']);
            });

            $rows = DB::table('feedback')->get();
            foreach ($rows as $row) {
                DB::table('feedback_tmp_guest_nullable')->insert([
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
            Schema::rename('feedback_tmp_guest_nullable', 'feedback');
            Schema::enableForeignKeyConstraints();
        }
    }

    public function down(): void
    {
        // Intentionally left non-destructive for SQLite rebuilds.
    }

    private function dropForeignIfExists(string $table, string $constraint): void
    {
        $exists = DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $table)
            ->where('CONSTRAINT_NAME', $constraint)
            ->where('CONSTRAINT_TYPE', 'FOREIGN KEY')
            ->exists();

        if ($exists) {
            DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$constraint}`");
        }
    }
};
