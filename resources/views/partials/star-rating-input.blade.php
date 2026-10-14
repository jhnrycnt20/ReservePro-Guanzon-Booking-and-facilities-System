@php
    $ratingName = $ratingName ?? 'rating';
    $ratingValue = (int) ($ratingValue ?? old($ratingName, 5));
    $ratingValue = max(1, min(5, $ratingValue ?: 5));
    $ratingId = $ratingId ?? 'rpStarRating';
    $labels = [1 => 'Poor', 2 => 'Fair', 3 => 'Good', 4 => 'Very good', 5 => 'Excellent'];
@endphp
<div class="rp-star-rating" data-rp-star-rating>
    <input type="hidden" name="{{ $ratingName }}" value="{{ $ratingValue }}" data-rp-star-value required>
    <div class="rp-star-rating-stars" role="radiogroup" aria-label="Rating from 1 to 5 stars">
        @for($i = 1; $i <= 5; $i++)
            <button
                type="button"
                class="rp-star-rating-btn{{ $i <= $ratingValue ? ' is-active' : '' }}"
                data-rp-star="{{ $i }}"
                aria-label="{{ $i }} star{{ $i > 1 ? 's' : '' }} — {{ $labels[$i] }}"
                aria-pressed="{{ $i === $ratingValue ? 'true' : 'false' }}"
            >
                <i class="bi bi-star{{ $i <= $ratingValue ? '-fill' : '' }}" aria-hidden="true"></i>
            </button>
        @endfor
    </div>
    <div class="rp-star-rating-label" data-rp-star-label>{{ $labels[$ratingValue] }}</div>
</div>
