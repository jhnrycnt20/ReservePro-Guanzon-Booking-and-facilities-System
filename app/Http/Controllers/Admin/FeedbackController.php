<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    public function index(Request $request): View
    {
        $query = Feedback::query()->with(['guest.user', 'booking', 'accommodation'])->latest();

        if ($request->filled('rating')) {
            $query->where('rating', (int) $request->input('rating'));
        }

        if ($request->filled('q')) {
            $q = trim((string) $request->input('q'));
            $query->where(function ($builder) use ($q) {
                $builder->where('comment', 'like', "%{$q}%")
                    ->orWhereHas('guest.user', function ($userQuery) use ($q) {
                        $userQuery->where('name', 'like', "%{$q}%")
                            ->orWhere('email', 'like', "%{$q}%");
                    })
                    ->orWhereHas('booking', function ($bookingQuery) use ($q) {
                        $bookingQuery->where('booking_number', 'like', "%{$q}%")
                            ->orWhere('guest_name', 'like', "%{$q}%");
                    });
            });
        }

        $feedback = $query->paginate(20)->withQueryString();
        $average = round((float) Feedback::query()->avg('rating'), 2);

        return view('admin.feedback.index', compact('feedback', 'average'));
    }
}
