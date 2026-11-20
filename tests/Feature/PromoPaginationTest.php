<?php

namespace Tests\Feature;

use App\Models\Promo;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromoPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_shows_ten_promos_per_page(): void
    {
        $role = Role::query()->create(['name' => 'Administrator', 'slug' => 'admin']);
        $admin = User::query()->create([
            'role_id' => $role->id,
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'StrongPass123',
            'is_active' => true,
        ]);

        foreach (range(1, 11) as $number) {
            Promo::query()->create([
                'code' => "PROMO{$number}",
                'name' => "Promo {$number}",
                'discount_percent' => 10,
                'applies_to_all' => true,
                'is_active' => true,
            ]);
        }

        $this->actingAs($admin)
            ->get(route('admin.promos.index'))
            ->assertOk()
            ->assertViewHas('promos', fn ($promos) => $promos->perPage() === 10
                && $promos->count() === 10
                && $promos->total() === 11);
    }
}
