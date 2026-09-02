<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

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

        $subject = $data['subject'] ?? ('New inquiry from ' . $data['name']);

        Mail::raw($data['message'], function ($mail) use ($data, $subject) {
            $mail->to('info@guanzonresort.com')
                ->subject($subject)
                ->replyTo($data['email'], $data['name']);
        });

        return back()->with('success', "Thanks for reaching out, {$data['name']} — we'll get back to you shortly.");
    }
}
