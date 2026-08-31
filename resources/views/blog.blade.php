@extends('layouts.public')

@section('title', 'Blog')

@section('content')
<section class="rp-page-hero">
    <div class="container">
        <div class="rp-kicker">ReservePro Journal</div>
        <h1>Stories from the resort</h1>
        <p>Tips for your stay, updates from Guanzon, and news from the ReservePro team.</p>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row g-4">
            @foreach ([
                [
                    'title' => 'Five ways to make the most of your Guanzon stay',
                    'excerpt' => 'From sunrise walks to the best cottages for families, here is how returning guests plan their visit.',
                    'date' => 'Aug 12, 2026',
                    'tag' => 'Guest Guide',
                    'image' => 'https://images.unsplash.com/photo-1499696010180-025ef6e1a8f9?auto=format&fit=crop&w=900&q=80',
                ],
                [
                    'title' => 'What happens after you submit a reservation',
                    'excerpt' => 'A look at the approval, payment verification, and check-in workflow behind every booking.',
                    'date' => 'Jul 28, 2026',
                    'tag' => 'How It Works',
                    'image' => 'https://images.unsplash.com/photo-1445019980597-93fa8acb246c?auto=format&fit=crop&w=900&q=80',
                ],
                [
                    'title' => 'Reporting an issue during your stay',
                    'excerpt' => 'Broken amenity or safety concern? Here is how guest reports reach front desk and security in real time.',
                    'date' => 'Jul 09, 2026',
                    'tag' => 'Guest Guide',
                    'image' => 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=900&q=80',
                ],
                [
                    'title' => 'Behind the scenes: preventing double bookings',
                    'excerpt' => 'How ReservePro checks availability at reservation and approval time to keep every stay conflict-free.',
                    'date' => 'Jun 21, 2026',
                    'tag' => 'Product',
                    'image' => 'https://images.unsplash.com/photo-1519643381401-22c77e60520e?auto=format&fit=crop&w=900&q=80',
                ],
                [
                    'title' => 'A guide to Guanzon\'s accommodation types',
                    'excerpt' => 'Rooms, cottages, and function halls — what fits a weekend trip versus a family reunion.',
                    'date' => 'Jun 03, 2026',
                    'tag' => 'Guest Guide',
                    'image' => 'https://images.unsplash.com/photo-1584132967334-10e028bd69f7?auto=format&fit=crop&w=900&q=80',
                ],
                [
                    'title' => 'Front desk tips for a smooth check-in',
                    'excerpt' => 'What to bring and what to expect when you arrive, from ID checks to walk-in availability.',
                    'date' => 'May 17, 2026',
                    'tag' => 'Guest Guide',
                    'image' => 'https://images.unsplash.com/photo-1455587734955-081b22074882?auto=format&fit=crop&w=900&q=80',
                ],
            ] as $post)
                <div class="col-md-6 col-lg-4">
                    <article class="rp-blog-card">
                        <div class="rp-blog-card-image" style="background-image: url('{{ $post['image'] }}');"></div>
                        <div class="rp-blog-card-body">
                            <div class="rp-blog-card-meta">
                                <span class="rp-blog-card-tag">{{ $post['tag'] }}</span>
                                <span>{{ $post['date'] }}</span>
                            </div>
                            <h2 class="rp-blog-card-title">{{ $post['title'] }}</h2>
                            <p class="rp-blog-card-excerpt">{{ $post['excerpt'] }}</p>
                        </div>
                    </article>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endsection
