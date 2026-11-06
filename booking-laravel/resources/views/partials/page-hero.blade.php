{{-- Header berwarna untuk halaman dalam. Parameter: $eyebrow, $title, $subtitle, $crumb --}}
<section class="page-hero">
    <span class="orb" style="width:220px;height:220px;right:-60px;top:-60px"></span>
    <span class="orb sky" style="width:90px;height:90px;right:22%;bottom:30px;animation-delay:-4s"></span>
    <div class="plus-3d d-none d-md-block" style="right:12%;top:40px;opacity:.6" aria-hidden="true"></div>
    <div class="container position-relative">
        @isset($crumb)
            <div class="crumbs">{!! $crumb !!}</div>
        @endisset
        @isset($eyebrow)
            <span class="eyebrow mt-2"><i class="fas {{ $eyebrowIcon ?? 'fa-spa' }}"></i> {{ $eyebrow }}</span>
        @endisset
        <h1>{{ $title }}</h1>
        @isset($subtitle)
            <p>{{ $subtitle }}</p>
        @endisset
    </div>
</section>
