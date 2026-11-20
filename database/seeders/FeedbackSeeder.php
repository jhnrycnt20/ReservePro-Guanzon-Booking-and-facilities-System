<?php

namespace Database\Seeders;

use App\Models\Accommodation;
use App\Models\Feedback;
use App\Models\Guest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class FeedbackSeeder extends Seeder
{
    public function run(): void
    {
        $guestRole = Role::query()->where('slug', 'guest')->firstOrFail();
        $password = config('demo.password', 'password');

        $entries = [
            [
                'name' => 'Maria Santos',
                'email' => 'maria.santos@reservepro.test',
                'phone' => '09170000011',
                'accommodation' => 'Beach House',
                'rating' => 5,
                'comment' => 'The beach house was clean, spacious, and perfect for our family weekend. The staff were very welcoming.',
                'days_ago' => 3,
            ],
            [
                'name' => 'Carlo Reyes',
                'email' => 'carlo.reyes@reservepro.test',
                'phone' => '09170000012',
                'accommodation' => 'Villa',
                'rating' => 4,
                'comment' => 'A relaxing stay with a beautiful view. Check-in was quick and the villa had everything we needed.',
                'days_ago' => 7,
            ],
            [
                'name' => 'Angela Cruz',
                'email' => 'angela.cruz@reservepro.test',
                'phone' => '09170000013',
                'accommodation' => 'Cabana',
                'rating' => 5,
                'comment' => 'We loved the cabana and the peaceful atmosphere. We will definitely book again for our next celebration.',
                'days_ago' => 12,
            ],
            [
                'name' => 'Miguel Garcia',
                'email' => 'miguel.garcia@reservepro.test',
                'phone' => '09170000014',
                'accommodation' => 'Suite Room',
                'rating' => 4,
                'comment' => 'The suite was comfortable and well maintained. The resort team responded quickly whenever we needed help.',
                'days_ago' => 18,
            ],
            [
                'name' => 'Sofia Mendoza',
                'email' => 'sofia.mendoza@reservepro.test',
                'phone' => '09170000015',
                'accommodation' => 'Open Cottage',
                'rating' => 5,
                'comment' => 'Great place for a day trip with friends. The cottage was tidy and conveniently located near the pool.',
                'days_ago' => 24,
            ],
            [
                'name' => 'Daniel Flores',
                'email' => 'daniel.flores@reservepro.test',
                'phone' => '09170000016',
                'accommodation' => null,
                'rating' => 3,
                'comment' => 'The resort grounds were lovely and the staff were friendly. More food choices would make the experience even better.',
                'days_ago' => 31,
            ],
        ];

        foreach ($entries as $entry) {
            $user = User::query()->updateOrCreate(
                ['email' => $entry['email']],
                [
                    'role_id' => $guestRole->id,
                    'name' => $entry['name'],
                    'phone' => $entry['phone'],
                    'password' => Hash::make($password),
                    'is_active' => true,
                ]
            );

            $guest = Guest::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'contact_number' => $entry['phone'],
                    'address' => 'Pangasinan, Philippines',
                ]
            );

            $accommodation = $entry['accommodation']
                ? Accommodation::query()->where('name', 'like', $entry['accommodation'].'%')->first()
                : null;

            $feedback = Feedback::query()->updateOrCreate(
                [
                    'guest_id' => $guest->id,
                    'booking_id' => null,
                    'scope' => $accommodation ? 'room' : 'resort',
                    'accommodation_id' => $accommodation?->id,
                ],
                [
                    'rating' => $entry['rating'],
                    'comment' => $entry['comment'],
                ]
            );

            $submittedAt = now()->subDays($entry['days_ago'])->setTime(10, 0);
            $feedback->forceFill([
                'created_at' => $submittedAt,
                'updated_at' => $submittedAt,
            ])->saveQuietly();
        }
    }
}
