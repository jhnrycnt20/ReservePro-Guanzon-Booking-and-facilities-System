<?php

namespace Tests\Feature;

use App\Models\Accommodation;
use App\Models\AccommodationType;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AccommodationValidationTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $role = Role::query()->create(['name' => 'Administrator', 'slug' => 'admin']);

        return User::query()->create([
            'role_id' => $role->id,
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'StrongPass123',
            'is_active' => true,
        ]);
    }

    private function payload(AccommodationType $type, array $overrides = []): array
    {
        return array_replace([
            'accommodation_type_id' => $type->id,
            'name' => 'Garden Cabin',
            'number' => '1',
            'capacity' => 4,
            'rate' => '1500.00',
            'status' => 'available',
            'is_active' => '1',
        ], $overrides);
    }

    private function image(int $width, int $height): UploadedFile
    {
        $chunk = static function (string $type, string $data): string {
            return pack('N', strlen($data)).$type.$data.pack('N', crc32($type.$data));
        };
        $row = "\x00".str_repeat("\x00\x00\x00", $width);
        $png = "\x89PNG\r\n\x1a\n"
            .$chunk('IHDR', pack('NNCCCCC', $width, $height, 8, 2, 0, 0, 0))
            .$chunk('IDAT', gzcompress(str_repeat($row, $height)))
            .$chunk('IEND', '');

        return UploadedFile::fake()->createWithContent('image.png', $png);
    }

    public function test_create_accepts_numbers_from_one_to_ten_and_rejects_duplicates_across_types(): void
    {
        Storage::fake('public');
        $admin = $this->admin();
        $cabin = AccommodationType::query()->create(['name' => 'Cabin', 'slug' => 'cabin']);
        $room = AccommodationType::query()->create(['name' => 'Room', 'slug' => 'room']);

        $this->actingAs($admin)->post(route('admin.accommodations.store'), $this->payload($cabin, [
            'number' => '1',
            'image' => $this->image(800, 600),
        ]))->assertSessionHasNoErrors();

        $created = Accommodation::query()->firstOrFail();
        $this->assertSame('1', $created->number);
        Storage::disk('public')->assertExists($created->image);

        $this->actingAs($admin)->post(route('admin.accommodations.store'), $this->payload($room, [
            'number' => '1',
        ]))->assertSessionHasErrors(['number']);

        $this->assertDatabaseCount('accommodations', 1);
    }

    public function test_create_rejects_out_of_range_number_capacity_rate_and_image_dimensions(): void
    {
        $admin = $this->admin();
        $type = AccommodationType::query()->create(['name' => 'Cabin', 'slug' => 'cabin']);

        $this->actingAs($admin)->post(route('admin.accommodations.store'), $this->payload($type, [
            'number' => '11',
            'capacity' => 51,
            'rate' => '0',
            'image' => $this->image(400, 300),
            'gallery' => [$this->image(200, 150)],
            '_modal' => 'acc_create',
        ]))->assertSessionHasErrors(['number', 'capacity', 'rate', 'image', 'gallery.0']);

        $response = $this->get(route('admin.accommodations.index'))->assertOk();
        $this->assertTrue(str_contains($response->getContent(), 'The cover image must be at least 800 × 600 pixels.'));
        $this->assertTrue(str_contains($response->getContent(), 'Each gallery image must be at least 400 × 300 pixels.'));

        $this->assertDatabaseCount('accommodations', 0);
    }

    public function test_inactive_listing_may_have_zero_rate_but_activation_requires_a_positive_rate(): void
    {
        $admin = $this->admin();
        $type = AccommodationType::query()->create(['name' => 'Cabin', 'slug' => 'cabin']);

        $this->actingAs($admin)->post(route('admin.accommodations.store'), $this->payload($type, [
            'rate' => '0',
            'is_active' => '0',
        ]))->assertSessionHasNoErrors();

        $accommodation = Accommodation::query()->firstOrFail();
        $this->assertFalse($accommodation->is_active);

        $this->actingAs($admin)->put(route('admin.accommodations.update', $accommodation), $this->payload($type, [
            'rate' => '0',
        ]))->assertSessionHasErrors(['rate']);
    }

    public function test_edit_enforces_total_gallery_limit_after_removals(): void
    {
        Storage::fake('public');
        $admin = $this->admin();
        $type = AccommodationType::query()->create(['name' => 'Cabin', 'slug' => 'cabin']);
        $accommodation = Accommodation::query()->create([
            ...$this->payload($type),
            'gallery' => array_map(fn ($index) => "accommodations/photo-{$index}.jpg", range(1, 20)),
        ]);

        $this->actingAs($admin)->put(route('admin.accommodations.update', $accommodation), $this->payload($type, [
            'gallery' => [$this->image(400, 300)],
        ]))->assertSessionHasErrors(['gallery']);

        $this->actingAs($admin)->put(route('admin.accommodations.update', $accommodation), $this->payload($type, [
            'gallery' => [$this->image(400, 300)],
            'remove_gallery' => ['accommodations/photo-1.jpg'],
        ]))->assertSessionHasNoErrors();

        $this->assertCount(20, $accommodation->fresh()->gallery);
    }
}
