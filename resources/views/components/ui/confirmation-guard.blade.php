{{-- Una acción protegida no puede saltarse la confirmación si falla la carga del bundle. --}}
<script>
    (() => {
        const detenerSinDialogo = event => {
            const origin = event.type === 'submit'
                ? (event.submitter?.hasAttribute('data-rm-confirm') ? event.submitter : event.target)
                : event.target.closest?.('[data-rm-confirm]');
            if (!origin?.getAttribute('data-rm-confirm')) return;
            if (typeof window.rmConfirmar === 'function') {
                document.getElementById('rm-confirmation-unavailable')?.remove();
                return;
            }
            event.preventDefault();
            event.stopImmediatePropagation();
            if (document.getElementById('rm-confirmation-unavailable')) return;
            const aviso = document.createElement('p');
            aviso.id = 'rm-confirmation-unavailable';
            aviso.className = 'rm-alert rm-alert--danger rm-confirmation-unavailable';
            aviso.setAttribute('role', 'alert');
            aviso.textContent = 'La confirmación no está disponible. Esta acción no se ha enviado; conserva tu captura antes de recargar la página.';
            document.body.append(aviso);
        };
        document.addEventListener('click', detenerSinDialogo, true);
        document.addEventListener('submit', detenerSinDialogo, true);
    })();
</script>
