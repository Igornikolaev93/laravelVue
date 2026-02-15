@extends('layouts.app')

@section('content')
<style>
    .reviews-main-container { padding: 10px 30px; }
    .reviews-header { display: flex; justify-content: flex-end; margin-bottom: 20px; }
    .reviews-header-icon { font-size: 20px; color: #909AB4; }
    .review-card-new {
        background-color: #F7F8FA;
        border: 1px solid #E5E5E5;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        max-width: 750px;
        animation: fadeIn 0.5s ease-out;
    }
    .review-card-new-header {
        display: flex;
        align-items: center;
        color: #6C757D;
        font-size: 14px;
        margin-bottom: 15px;
        gap: 15px;
        flex-wrap: wrap;
    }
    .review-card-new-author strong { font-size: 15px; font-weight: 600; }
    .review-card-new-text { line-height: 1.6; color: #363740; font-size: 14px; }
    .no-reviews, .fetch-status { text-align: center; padding: 40px; color: #6C757D; }
    .rating-stars { display: flex; gap: 2px; margin: 10px 0; }
    .star-filled { color: #FBBC04; }
    .star-empty { color: #DCE4EA; }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<div class="reviews-main-container">
    @if ($settings && $settings->yandex_maps_url)
        <div class="reviews-header">
            <span class="reviews-header-icon"><i class="fas fa-external-link-alt"></i></span>
        </div>
        
        <div id="reviewsStats" style="margin-bottom: 20px; display: none;">
            <div style="display: flex; align-items: center; gap: 20px; flex-wrap: wrap;">
                <div style="font-size: 24px; font-weight: 600; color: #363740;" id="averageRating"></div>
                <div id="ratingStars" class="rating-stars"></div>
                <div style="color: #6C757D; font-size: 14px;" id="totalReviews"></div>
            </div>
        </div>
        
        <div id="fetchStatus" class="fetch-status" style="display: none;"></div>
        <div id="reviewsList"></div>

        <script>
            document.addEventListener('DOMContentLoaded', ()=>loadYandexReviews('{{ $settings->yandex_maps_url }}'));

            async function loadYandexReviews(url) {
                const s=document.getElementById('fetchStatus'), l=document.getElementById('reviewsList'), stats=document.getElementById('reviewsStats');
                s.style.display='block'; s.textContent='Загрузка отзывов...'; l.innerHTML='';
                
                try {
                    const r=await fetch('{{ route("yandex-maps.fetch-reviews") }}',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify({url})});
                    const d=await r.json();
                    if(!r.ok) throw new Error(d.error||'Ошибка загрузки');
                    
                    s.style.display='none';
                    if(d.reviews?.length) {
                        if(d.stats) {
                            stats.style.display='block';
                            document.getElementById('averageRating').textContent=d.stats.average_rating||'0';
                            document.getElementById('ratingStars').innerHTML=renderStars(parseFloat(d.stats.average_rating||0));
                            document.getElementById('totalReviews').textContent=`Всего отзывов: ${d.stats.total_reviews||d.reviews.length}`;
                        }
                        renderReviews(d.reviews);
                    } else l.innerHTML='<div class="no-reviews">Нет отзывов</div>';
                } catch(e) { s.textContent=`❌ Ошибка: ${e.message}`; console.error(e); }
            }

            function renderStars(r) {
                let s='';
                for(let i=0;i<5;i++) s+= i < r ? '<span class="star-filled">★</span>' : '<span class="star-empty">★</span>';
                return s;
            }

            function renderReviews(reviews) {
                document.getElementById('reviewsList').innerHTML=reviews.map(r=>{
                    const d=r.date?new Date(r.date):new Date();
                    return `<div class="review-card-new">
                        <div class="review-card-new-header">
                            <span>${!isNaN(d)?new Intl.DateTimeFormat('ru-RU',{year:'numeric',month:'2-digit',day:'2-digit',hour:'2-digit',minute:'2-digit'}).format(d):'Дата не указана'}</span>
                            <span class="branch">филиал 1</span>
                        </div>
                        <div class="review-card-new-author"><strong>${escape(r.author||'Аноним')}</strong></div>
                        <div class="rating-stars">${renderStars(r.rating||0)}</div>
                        <div class="review-card-new-text">${escape(r.text||'Нет текста')}</div>
                    </div>`;
                }).join('');
            }

            const escape=(u)=>u?u.replace(/[&<>]|(?<=[^\\])"/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'})[m]):'';
        </script>
    @else
        <div class="no-reviews">
            <p>Чтобы увидеть отзывы, перейдите в раздел "Настройка" и подключите Яндекс.Карты.</p>
        </div>
    @endif
</div>
@endsection
