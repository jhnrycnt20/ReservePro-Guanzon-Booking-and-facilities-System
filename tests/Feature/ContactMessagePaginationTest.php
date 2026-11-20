<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactMessagePaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_shows_ten_contact_messages_per_page(): void
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
            ContactMessage::query()->create([
                'name' => "Sender {$number}",
                'email' => "sender{$number}@example.com",
                'subject' => "Message {$number}",
                'message' => "Contact message {$number}",
            ]);
        }

        $this->actingAs($admin)
            ->get(route('admin.contact-messages.index'))
            ->assertOk()
            ->assertViewHas('messages', fn ($messages) => $messages->perPage() === 10
                && $messages->count() === 10
                && $messages->total() === 11);
    }
}
