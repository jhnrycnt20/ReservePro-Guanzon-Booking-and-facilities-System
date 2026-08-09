<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    public function index(): View
    {
        $feedback = Feedback::query()->with(['guest.user', 'booking'])->latest()->paginate(20);
        $average = round((float) Feedback::query()->avg('rating'), 2);

        return view('admin.feedback.index', compact('feedback', 'average'));
    }
}
