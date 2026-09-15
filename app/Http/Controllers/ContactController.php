<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ContactController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'subject' => ['nullable', 'string', 'max:150'],
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $subject = $data['subject'] ?: ('New inquiry from '.$data['name']);

        ContactMessage::query()->create([
            'subject' => $subject,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?: null,
            'message' => $data['message'],
        ]);

        $to = SystemSetting::getValue('resort_email', 'info@guanzonresort.com');

        $body = implode("\n", [
            'Name: '.$data['name'],
            'Email: '.$data['email'],
            'Phone: '.($data['phone'] ?: 'N/A'),
            '',
            $data['message'],
        ]);

        try {
            Mail::raw($body, function ($mail) use ($data, $subject, $to) {
                $mail->to($to)
                    ->subject($subject)
                    ->replyTo($data['email'], $data['name']);
            });
        } catch (Throwable $e) {
            // Message is already saved; email is optional when SMTP is not configured.
            Log::warning('Contact form mail failed', [
                'error' => $e->getMessage(),
                'from' => $data['email'],
            ]);
        }

        return back()->with('success', "Thanks for reaching out, {$data['name']} — we'll get back to you shortly.");
    }
}
