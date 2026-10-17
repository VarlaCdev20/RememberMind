<div data-rm-access-rejection hidden class="fixed inset-x-4 top-4 z-[var(--rm-z-toast)] mx-auto max-w-xl shadow-[var(--rm-shadow-lg)]">
    <x-ui.callout variant="warning" title="Solicitud rechazada" role="alert">
        <p>El servidor no autorizó esta solicitud. Si estabas guardando, el resultado no está confirmado. Revisa tu acceso antes de intentarlo de nuevo.</p>
        <p class="mt-2 text-xs">Al volver al inicio perderás los cambios sin guardar.</p>
        <a data-rm-access-home href="{{ route('dashboard') }}" class="rm-btn-secondary mt-3 inline-flex min-h-10 items-center px-3">
            Volver a mi inicio
        </a>
    </x-ui.callout>
</div>
