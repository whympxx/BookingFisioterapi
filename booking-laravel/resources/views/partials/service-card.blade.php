@php($bookUrl = auth()->check() ? route('booking.create', ['layanan' => $service->id]) : route('login'))
<article class="svc-card" data-tilt="7">
    @if ($service->is_popular)
        <span class="svc-ribbon"><i class="fas fa-fire mr-1"></i>Terpopuler</span>
    @endif
    <div class="svc-media">
        <img src="{{ $service->imageUrl() }}" alt="{{ $service->name }}" loading="lazy">
    </div>
    <div class="svc-icon tilt-pop"><i class="fas {{ $service->icon }}"></i></div>
    <div class="svc-body">
        <h3>{{ $service->name }}</h3>
        <p>{{ $service->description }}</p>
        <ul class="svc-features">
            @foreach ($service->features as $feature)
                <li><i class="fas fa-circle-check"></i> {{ $feature }}</li>
            @endforeach
        </ul>
        <div class="svc-foot">
            <div class="price">{{ $service->formattedPrice() }}<small><i class="far fa-clock mr-1"></i>{{ $service->duration }} / sesi</small></div>
            <a href="{{ $bookUrl }}" class="btn {{ $service->is_popular ? 'btn-primary' : 'btn-light-soft' }} btn-sm">Booking <i class="fas fa-arrow-right ml-1"></i></a>
        </div>
    </div>
</article>
