<div class="flip" tabindex="0" role="button" aria-label="Lihat profil {{ $therapist->name }}">
    <div class="flip-inner">
        <div class="flip-face">
            @if ($therapist->photoUrl())
                <img src="{{ $therapist->photoUrl() }}" alt="{{ $therapist->name }}" class="avatar-3d">
            @else
                <div class="avatar-3d" aria-hidden="true">{{ $therapist->initials() }}</div>
            @endif
            <h5>{{ $therapist->name }}</h5>
            <div class="spec">{{ $therapist->specialization }}</div>
            <div class="mt-2"><span class="badge badge-soft"><i class="fas fa-award mr-1"></i>{{ $therapist->experience_years }} tahun pengalaman</span></div>
            <div class="flip-hint"><i class="fas fa-rotate mr-1"></i>Arahkan / ketuk untuk detail</div>
        </div>
        <div class="flip-face flip-back">
            <i class="fas fa-quote-left fa-2x mb-3" style="opacity:.5"></i>
            <h5>{{ $therapist->name }}</h5>
            <p class="mb-3">{{ $therapist->bio ?: 'Fisioterapis berlisensi yang siap membantu pemulihan Anda.' }}</p>
            <span class="badge" style="background:rgba(255,255,255,.2);color:#fff">{{ $therapist->specialization }}</span>
        </div>
    </div>
</div>
