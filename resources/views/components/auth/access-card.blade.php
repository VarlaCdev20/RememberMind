@props(['label' => 'Acceso institucional'])
@php
    $fotoAcceso = app(\App\Modulos\Reportes\Presentacion\DashboardPhotoRotation::class)->heroImage('login', asset('images/dashboard/enfermera-manos.webp'));
@endphp
<section {{ $attributes->class(['rm-auth-card']) }} aria-label="{{ $label }}">
    <aside class="rm-auth-brand" aria-label="Portal institucional">
        <div class="rm-auth-brand__identity">
            <img src="{{ asset('storage/imagenes/LOGO.png') }}" alt="Logo del centro" width="48" height="48">
            <div><strong>RememberMind</strong><span>Centro Geriátrico Los Almendros</span></div>
        </div>
        <figure class="rm-auth-photo">
            <img src="{{ $fotoAcceso }}" alt="Actividades y momentos compartidos en el Centro Geriátrico Los Almendros" width="1600" height="1000" fetchpriority="high" decoding="async" data-auth-photo>
        </figure>
        <div class="rm-auth-brand__message">
            <h2>Cuidado, memoria<br>y dignidad.</h2>
            <p>Acompañamos cada día el bienestar de nuestros residentes.</p>
        </div>
    </aside>
    <section class="rm-auth-form-panel" aria-label="{{ $label }}">
        <div class="rm-auth-brand__identity rm-auth-mobile-brand">
            <img src="{{ asset('storage/imagenes/LOGO.png') }}" alt="Logo del centro" width="48" height="48">
            <div><strong>RememberMind</strong><span>Acceso institucional</span></div>
        </div>
        {{ $slot }}
    </section>
</section>
