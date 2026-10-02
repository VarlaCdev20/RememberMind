@php
    $tipo = session()->has('success') ? 'success' : (session()->has('error') ? 'danger' : (session()->has('warning') ? 'warning' : (session()->has('info') ? 'info' : null)));
    $mensaje = $tipo ? session($tipo === 'danger' ? 'error' : $tipo) : null;
    $icono = ['success' => 'ph-check-circle', 'danger' => 'ph-warning-circle', 'warning' => 'ph-warning', 'info' => 'ph-info'][$tipo ?? 'info'];
@endphp
@if($tipo && $mensaje)
    <div class="rm-toast-region" x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" x-transition role="status" aria-live="polite">
        <div class="rm-toast rm-toast-{{ $tipo }}">
            <i class="ph {{ $icono }}" aria-hidden="true"></i>
            <p>{{ $mensaje }}</p>
            <button type="button" class="rm-btn-icon" @click="show = false" aria-label="Cerrar notificación"><i class="ph ph-x" aria-hidden="true"></i></button>
        </div>
    </div>
@endif
