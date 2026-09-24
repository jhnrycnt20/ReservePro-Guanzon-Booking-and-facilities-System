@extends('layouts.public')

@section('title', 'My Feedback')

@section('content')
<div class="container rp-public-page-top pb-4">
    <div class="rp-page-intro d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div>
            <h1 class="rp-page-intro-title mb-1">My Feedback</h1>
            <p class="text-muted mb-0">Feedback you sent about rooms or the resort.</p>
        </div>
        <a href="{{ route('guest.feedback.create') }}" class="btn btn-rp-primary">Write Feedback</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="rp-flow-card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>About</th>
                        <th>Rating</th>
                        <th>Comment</th>
                        <th>Submitted</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($feedback as $item)
                        <tr>
                            <td>
                                @if($item->booking)
                                    Stay {{ $item->booking->booking_number }}
                                    @if($item->booking->accommodation)
                                        <div class="small text-muted">{{ $item->booking->accommodation->name }}</div>
                                    @endif
                                @elseif($item->scope === 'room')
                                    Room: {{ $item->accommodation->name ?? 'Room' }}
                                @else
                                    Resort
                                @endif
                            </td>
                            <td>
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="bi bi-star{{ $i <= $item->rating ? '-fill text-warning' : '' }}"></i>
                                @endfor
                            </td>
                            <td>{{ $item->comment ?: '—' }}</td>
                            <td>{{ $item->created_at?->format('M d, Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-muted py-4">
                                You have not submitted feedback yet.
                                <a href="{{ route('guest.feedback.create') }}">Write feedback</a> about a room or the resort.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($feedback, 'links'))
            <div class="mt-3">{{ $feedback->withQueryString()->links() }}</div>
        @endif
    </div>
</div>
@endsection
