<?php

namespace Database\Seeders;

use App\Models\ContactMessage;
use Illuminate\Database\Seeder;

class ContactMessageSeeder extends Seeder
{
    public function run(): void
    {
        $messages = [
            [
                'name' => 'Patricia Lim',
                'email' => 'patricia.lim@example.com',
                'phone' => '09171234561',
                'subject' => 'Family reunion package inquiry',
                'message' => 'We are planning a family reunion for around 30 guests. Could you send the available accommodation and catering packages?',
                'days_ago' => 1,
                'read' => false,
            ],
            [
                'name' => 'Joshua Aquino',
                'email' => 'joshua.aquino@example.com',
                'phone' => '09181234562',
                'subject' => 'December weekend availability',
                'message' => 'Are any villas or beach houses available for the second weekend of December? We will be a group of eight.',
                'days_ago' => 2,
                'read' => false,
            ],
            [
                'name' => 'Camille Torres',
                'email' => 'camille.torres@example.com',
                'phone' => '09191234563',
                'subject' => 'Corporate team-building event',
                'message' => 'Our company is looking for a venue for a one-day team-building activity for 45 employees. Please share your group rates and facilities.',
                'days_ago' => 4,
                'read' => true,
            ],
            [
                'name' => 'Nathan Villanueva',
                'email' => 'nathan.villanueva@example.com',
                'phone' => '09201234564',
                'subject' => 'Day tour rates and inclusions',
                'message' => 'May I know the current day tour rate for adults and children, including the pool and cottage inclusions?',
                'days_ago' => 6,
                'read' => true,
            ],
            [
                'name' => 'Elaine Ramos',
                'email' => 'elaine.ramos@example.com',
                'phone' => null,
                'subject' => 'Accessibility question',
                'message' => 'Do you have wheelchair-accessible rooms and pathways near the main pool? We are traveling with an elderly family member.',
                'days_ago' => 9,
                'read' => false,
            ],
            [
                'name' => 'Marco Dela Cruz',
                'email' => 'marco.delacruz@example.com',
                'phone' => '09221234566',
                'subject' => 'Lost item after checkout',
                'message' => 'I may have left a black insulated bottle in our room after checking out. Could your team please check the lost-and-found?',
                'days_ago' => 12,
                'read' => true,
            ],
            [
                'name' => 'Bianca Navarro',
                'email' => 'bianca.navarro@example.com',
                'phone' => '09231234567',
                'subject' => 'Wedding reception venue',
                'message' => 'We are considering Guanzon Resort for an intimate wedding reception next year. Can we schedule an ocular visit and discuss packages?',
                'days_ago' => 16,
                'read' => true,
            ],
        ];

        foreach ($messages as $index => $data) {
            $receivedAt = now()->subDays($data['days_ago'])->setTime(9 + ($index % 7), 15);

            $message = ContactMessage::query()->updateOrCreate(
                [
                    'email' => $data['email'],
                    'subject' => $data['subject'],
                ],
                [
                    'name' => $data['name'],
                    'phone' => $data['phone'],
                    'message' => $data['message'],
                    'read_at' => $data['read'] ? $receivedAt->copy()->addHours(2) : null,
                ]
            );

            $message->forceFill([
                'created_at' => $receivedAt,
                'updated_at' => $receivedAt,
            ])->saveQuietly();
        }
    }
}
