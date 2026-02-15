@extends('layouts.app')

@section('content')
<style>
    .url-form-container form { display: flex; align-items: center; gap: 10px; margin-bottom: 20px; }
    .url-input { width: 480px; height: 38px; padding: 6px 14px; border: 1px solid #DCE4EA; border-radius: 6px; }
    .submit-button { background: #339AF0; color: white; border: none; border-radius: 6px; padding: 10px 20px; font-weight: 600; cursor: pointer; height: 38px; }
    .rating-block { display: flex; align-items: center; gap: 20px; padding: 20px; background: #f6f8fa; border-radius: 6px; margin-bottom: 20px; }
    .stars { color: #ffc107; }
    .review-card { background: white; border: 1px solid #e5e5e5; border-radius: 6px; padding: 20px; margin-bottom: 20px; }
    .review-header { color: #6c757d; margin-bottom: 10px; display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
    .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
    .alert-danger { background: #f8d7da; color: #721c24; }
    .alert-success { background: #d4edda; color: #155724; }
    #loader { text-align: center; padding: 40px; }
</style>

<div class="reviews-container">
    <div class="url-form-container">
        <form action="{{ route('yandex-maps.index') }}" method="POST">
            @csrf
            <input type="text" name="yandex_maps_url" class="url-input" placeholder="Enter Yandex Maps URL" value="{{ $settings->yandex_maps_url ?? '' }}" required>
            <button type="submit" class="submit-button">Save URL</button>
        </form>
        @if ($errors->any() || session('error'))
            <div class="alert alert-danger">{{ $errors->first() ?? session('error') }}</div>
        @endif
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
    </div>

    @if ($settings && $settings->yandex_maps_url)
        <div id="rating-container"></div>
        <div id="reviews-list"></div>
        <div id="pagination-container"></div>
        <div id="loader">Loading reviews...</div>
    @endif
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const reviewsContainer = document.getElementById('reviews-container');
        if (reviewsContainer) {
            fetchReviews();
        }
    });

    function fetchReviews(page = 1) {
        const loader = document.getElementById('loader');
        const reviewsList = document.getElementById('reviews-list');
        const ratingContainer = document.getElementById('rating-container');
        const paginationContainer = document.getElementById('pagination-container');

        loader.style.display = 'block';
        reviewsList.innerHTML = '';

        fetch(`/api/yandex-maps/reviews?page=${page}`)
            .then(response => response.json())
            .then(data => {
                loader.style.display = 'none';
                
                if (data.error) {
                    reviewsList.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
                    return;
                }

                updateRating(data.rating, data.total_reviews);
                renderReviews(data.reviews);
                renderPagination(data.total_reviews, page);
            })
            .catch(error => {
                loader.style.display = 'none';
                console.error('Fetch error:', error);
                reviewsList.innerHTML = `<div class="alert alert-danger">An error occurred while loading reviews.</div>`;
            });
    }

    function updateRating(rating, totalReviews) {
        const ratingContainer = document.getElementById('rating-container');
        const ratingValue = parseFloat(rating);
        const stars = '★'.repeat(Math.round(ratingValue)) + '☆'.repeat(5 - Math.round(ratingValue));
        ratingContainer.innerHTML = `
            <div class="rating-block">
                <span class="platform-name">Яндекс Карты</span>
                <span class="rating-value">${ratingValue.toFixed(1)}</span>
                <div class="stars">${stars}</div>
                <span>${totalReviews} отзывов</span>
            </div>
        `;
    }

    function renderReviews(reviews) {
        const reviewsList = document.getElementById('reviews-list');
        if (reviews.length === 0) {
            reviewsList.innerHTML = '<div class="review-card">No reviews found.</div>';
            return;
        }

        let html = '';
        reviews.forEach(review => {
            const stars = review.rating ? '★'.repeat(Math.round(review.rating)) + '☆'.repeat(5 - Math.round(review.rating)) : '';
            html += `
                <div class="review-card">
                    <div class="review-header">
                        <span>${review.author}</span>
                        <span>${review.date}</span>
                        <div class="stars">${stars}</div>
                    </div>
                    <div>${review.text}</div>
                </div>
            `;
        });
        reviewsList.innerHTML = html;
    }

    function renderPagination(total, currentPage) {
        const paginationContainer = document.getElementById('pagination-container');
        const totalPages = Math.ceil(total / 5);
        if (totalPages <= 1) {
            paginationContainer.innerHTML = '';
            return;
        }

        let html = '<nav><ul class="pagination">';
        for (let i = 1; i <= totalPages; i++) {
            html += `<li class="page-item ${i === currentPage ? 'active' : ''}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
        }
        html += '</ul></nav>';
        paginationContainer.innerHTML = html;

        document.querySelectorAll('.page-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const page = this.getAttribute('data-page');
                fetchReviews(page);
            });
        });
    }
</script>
@endpush