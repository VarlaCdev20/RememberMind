@inject('rolePreview', 'App\Backend\Modulos\Identidad\Servicios\RolePreviewService')
@php
    $previewLabel = $rolePreview->label(auth()->user());
@endphp

@if($previewLabel)
    <section data-role-preview-banner class="sticky top-[64px] z-20 border-y border-amber-300 bg-amber-50 px-4 py-3 text-amber-950 shadow-sm" role="status" aria-live="polite">
        <div class="mx-auto flex max-w-[1440px] flex-wrap items-center justify-between gap-3">
            <div class="flex items-start gap-3">
                <i class="ph-bold ph-eye mt-0.5 text-xl" aria-hidden="true"></i>
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.14em]">Modo previsualización</p>
                    <p class="text-sm font-semibold">Estás viendo RememberMind como: {{ $previewLabel }}. Este modo es solo lectura y tu identidad sigue siendo Superadministrador.</p>
                </div>
            </div>
            <form method="POST" action="{{ route('role-preview.destroy') }}">
                @csrf
                @method('DELETE')
                <button type="submit" data-preview-allow class="rm-btn-secondary min-h-9 px-3 text-xs">
                    <i class="ph-bold ph-arrow-u-up-left" aria-hidden="true"></i>
                    Volver a Superadministrador
                </button>
            </form>
        </div>
    </section>
@endif
